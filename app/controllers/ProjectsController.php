<?php

class ProjectsController extends BaseController {

	public function show(Project $project)
	{
		$currentSprint = $project->currentSprint();

		return $currentSprint ? App::make('SprintsController')->show($currentSprint)
		                      : View::make('project.view', compact('project'));
	}

	public function index()
	{
		return View::make('project.index')->with('projects', Project::all());
	}
}
