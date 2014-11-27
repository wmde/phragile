<?php

class SprintsController extends BaseController {

	public function create(Project $project)
	{
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
