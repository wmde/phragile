<?php

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

		return Redirect::route('project_path', $project->slug);
	}
}
