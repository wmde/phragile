<?php
namespace Phragile\ActionHandler;

use Phragile\PhabricatorAPI;
use Flash;
use Sprint;
use Illuminate\Support\Facades\Redirect;

class SprintStoreActionHandler {
	private $userPhabricatorAPI;
	private $botPhabricatorAPI;
	private $redirect;
	private $sprint;
	private $user;

	public function __construct(PhabricatorAPI $userPhabricatorAPI, PhabricatorAPI $botPhabricatorAPI)
	{
		$this->userPhabricatorAPI = $userPhabricatorAPI;
		$this->botPhabricatorAPI = $botPhabricatorAPI;
	}

	public function performAction(Sprint $sprint, \User $user)
	{
		$this->sprint = $sprint;
		$this->user = $user;
		$this->user->setPhabricatorURL(env('PHABRICATOR_URL'));

		$this->validate();
		$this->trySprintCreationFromPhabricatorID();
		$this->connectUserToPhabricator();
		$this->createCorrespondingPhabricatorProject();
		$this->save(
			'Successfully created "' . $this->sprint->title . '"',
			'A problem occurred saving the sprint record in Phragile.'
		);
	}

	private function validate()
	{
		if ($this->previousActionFailed())
		{
			return;
		}

		$validation = $this->sprint->validate();
		if ($validation->fails())
		{
			Flash::error(implode(' ', $validation->messages()->all()));
			$this->redirect = Redirect::back();
		}
	}

	private function trySprintCreationFromPhabricatorID()
	{
		if ($this->previousActionFailed() || !ctype_digit($this->sprint->title))
		{
			return;
		}

		if ($this->sprintWithPhabricatorIDExists())
		{
			$this->redirectBackWithError('The sprint with this Phabricator ID already exists in Phragile.');
			return;
		}

		$phabricatorProject = $this->botPhabricatorAPI->queryProjectByID($this->sprint->title);
		if ($phabricatorProject)
		{
			$this->sprint->connectWithPhabricatorProject($phabricatorProject);
			$this->saveConnectedSprint();
		} else
		{
			$this->redirectBackWithError('A Phabricator project with this ID does not exist.');
		}
	}

	private function sprintWithPhabricatorIDExists()
	{
		return !Sprint::where('phabricator_id', $this->sprint->title)->get()->isEmpty();
	}

	private function connectUserToPhabricator()
	{
		if ($this->previousActionFailed())
		{
			return;
		}

		if ($this->user->apiTokenValid())
		{
			$this->userPhabricatorAPI->setConduitAPIToken($this->user->conduit_api_token);
		} else
		{
			$this->redirectBackWithError('Please make sure you are using a valid Conduit API token.');
		}
	}

	private function createCorrespondingPhabricatorProject()
	{
		if ($this->previousActionFailed())
		{
			return;
		}

		try
		{
			$phabricatorProject = $this->userPhabricatorAPI->createProject($this->sprint->title, [$this->user->phid]);
			$this->sprint->connectWithPhabricatorProject($phabricatorProject);
		} catch (\ConduitClientException $e)
		{
			$this->connectIfPhabricatorProjectExists($e->getMessage());
		}
	}

	private function connectIfPhabricatorProjectExists($errorMessage)
	{
		if (str_contains($errorMessage, ['Project name is already used', 'Project name generates the same hashtag']))
		{
			$this->connectWithPhabricatorProject();
		} else
		{
			$this->redirectBackWithError('Could not create a Phabricator project for this sprint.');
		}
	}

	private function connectWithPhabricatorProject()
	{
		$this->sprint->connectWithPhabricatorProject($this->fetchPhabricatorProject());
		$this->saveConnectedSprint();
	}

	private function saveConnectedSprint()
	{
		$this->save(
			'Connected "' . $this->sprint->title . '" with an existing Phabricator project',
			'Could not create the sprint or connect it with an existing Phabricator project.'
		);
	}

	private function fetchPhabricatorProject()
	{
		return $this->botPhabricatorAPI->queryProjectByTitle($this->sprint->title);
	}

	private function save($successMsg, $errorMsg)
	{
		if ($this->previousActionFailed())
		{
			return;
		}

		if ($this->sprint->save())
		{
			$this->redirectWithSuccessMessage($successMsg);
		} else
		{
			$this->redirectBackWithError($errorMsg);
		}
	}

	private function redirectBackWithError($msg)
	{
		Flash::error($msg);
		$this->redirect = Redirect::back();
	}

	private function redirectWithSuccessMessage($msg)
	{
		Flash::success($msg);
		$this->redirect = Redirect::route('sprint_path', ['sprint' => $this->sprint->phabricator_id]);
	}

	private function previousActionFailed()
	{
		return $this->redirect !== null;
	}

	public function getRedirect()
	{
		return $this->redirect;
	}
}
