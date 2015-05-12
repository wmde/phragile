<?php
namespace Phragile\ActionHandler;

use Phragile\PhabricatorAPI;
use \Flash;
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

	public function performAction(\Sprint $sprint, \User $user)
	{
		$this->sprint = $sprint;
		$this->user = $user;

		$this->validate();
		$this->connectUserToPhabricator();
		$this->createCorrespondingPhabricatorProject();
		$this->save();
	}

	private function validate()
	{
		if ($this->previousActionFailed()) return;

		$validation = $this->sprint->validate();
		if ($validation->fails())
		{
			Flash::error(implode(' ', $validation->messages()->all()));
			$this->redirect = Redirect::back();
		}
	}

	private function connectUserToPhabricator()
	{
		if ($this->previousActionFailed()) return;

		try
		{
			$this->userPhabricatorAPI->connect($this->user->username, $this->user->conduit_certificate);
		} catch(\ConduitClientException $e)
		{
			$this->redirectBackWithError($e->getMessage());
		}
	}

	private function createCorrespondingPhabricatorProject()
	{
		if ($this->previousActionFailed()) return;

		try
		{
			$phabricatorProject = $this->userPhabricatorAPI->createProject($this->sprint->title, [$this->user->phid]);
			$this->sprint->connectWithPhabricatorProject($phabricatorProject);
		} catch(\ConduitClientException $e)
		{
			$this->connectIfPhabricatorProjectExists($e->getMessage());
		}
	}

	private function connectIfPhabricatorProjectExists($errorMessage)
	{
		if (str_contains($errorMessage, 'Project name is already used'))
		{
			$this->connectWithPhabricatorProject();
		} else $this->redirectBackWithError('Could not create a Phabricator project for this sprint.');
	}

	private function connectWithPhabricatorProject()
	{
		$this->sprint->connectWithPhabricatorProject($this->fetchPhabricatorProject());
		if ($this->sprint->save()) $this->redirectWithSuccessMessage('Connected "' . $this->sprint->title . '" with an existing Phabricator project');
		else $this->redirectBackWithError('Could not create the sprint or connect it with an existing Phabricator project.');
	}

	private function fetchPhabricatorProject()
	{
		return $this->botPhabricatorAPI->queryProjectByTitle($this->sprint->title);
	}

	private function save()
	{
		if ($this->previousActionFailed()) return;

		if (!$this->sprint->save())
		{
			$this->redirectBackWithError('A problem occurred saving the sprint record in Phragile.');
		} else
		{
			$this->redirectWithSuccessMessage('Successfully created "' . $this->sprint->title . '"');
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
