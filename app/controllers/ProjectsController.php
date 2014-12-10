<?php

class ProjectsController extends BaseController {

	public function show(Project $project)
	{
		return App::make('SprintsController')->show($project->currentSprint());
	}
}
