<?php
namespace Phragile\ActionHandler;

use Phragile\PhabricatorAPI;
use \Flash;
use Illuminate\Support\Facades\Redirect;

class SprintStoreActionHandler {
	private $phabricatorAPI;
	private $redirect;
	private $sprint;
	private $user;

	public function __construct(PhabricatorAPI $phabricatorAPI)
	{
		$this->phabricatorAPI = $phabricatorAPI;
	}

	public function performAction(\Sprint $sprint, \User $user)
	{
		$this->sprint = $sprint;
		$this->user = $user;

		$this->validate();
		$this->connectToPhabricator();
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

	private function connectToPhabricator()
	{
		if ($this->previousActionFailed()) null;

		try
		{
			$this->phabricatorAPI->connect($this->user->username, $this->user->conduit_certificate);
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
			$phabricatorProject = $this->phabricatorAPI->createProject($this->sprint->title, [$this->user->phid]);
			$this->sprint->connectWithPhabricatorProject($phabricatorProject);
		} catch(\ConduitClientException $e)
		{
			$this->redirectBackWithError($e->getMessage());
		}
	}

	private function save()
	{
		if ($this->previousActionFailed()) return;

		if (!$this->sprint->save())
		{
			$this->redirectBackWithError('A problem occurred saving the sprint record in Phragile.');
		} else
		{
			Flash::success('Successfully created "' . $this->sprint->title . '"');
			$this->redirect = Redirect::route('sprint_path', ['sprint' => $this->sprint->phabricator_id]);
		}
	}

	private function redirectBackWithError($msg)
	{
		Flash::error($msg);
		$this->redirect = Redirect::back();
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
