<?php

class ProjectsController extends BaseController {

	public function show(Project $project)
	{
		return App::make('SprintsController')->show($project->currentSprint());
	}

	public function index()
	{
		return View::make('project.index')->with('projects', Project::all());
	}
}
