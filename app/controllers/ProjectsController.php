<?php

class ProjectsController extends BaseController {

	public function show(Project $project)
	{
		return View::make('project.view', compact('project'));
	}
}
