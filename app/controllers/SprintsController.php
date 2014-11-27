<?php

use Phragile\PhabricatorAPI;

class SprintsController extends BaseController {

	public function create(Project $project)
	{
		if (!Auth::user()->certificateValid())
		{
			Flash::warning('Please set a valid Conduit certificate before trying to create a new sprint.');
			return Redirect::back();
		}

		return View::make('sprint.create', compact('project'));
	}

	public function store(Project $project)
	{
		$sprint = Sprint::create(array_merge(
			Input::all(),
			['project_id' => $project->id]
		));

		if (!$sprint)
		{
			Flash::error('A problem occurred saving the sprint record in Phragile.');
			return Redirect::back();
		}

		return $this->createPhabricatorProject($sprint);
	}

	public function confirmation(Sprint $sprint)
	{
		return View::make('sprint.confirmation', compact('sprint'));
	}

	private function createPhabricatorProject(Sprint $sprint)
	{
		$phabricator = new PhabricatorAPI(new ConduitClient($_ENV['PHABRICATOR_URL']));
		$user = Auth::user();
		try
		{
			$phabricator->connect($user->username, $user->conduit_certificate);
			$response = $phabricator->createProject($sprint->title);
		} catch (ConduitClientException $e)
		{
			$sprint->delete();

			Flash::error('Failed to create a Phabricator for the sprint: ' . $e->getMessage());
			return Redirect::back();
		}

		$sprint->phid = $response['id'];
		$sprint->save();

		return Redirect::to('sprint_confirmation_path', [$sprint->phid]);
	}
}
