<?php

use Phragile\TaskList;

class ProjectsController extends BaseController {

	public function show(Project $project)
	{
		return App::make('SprintsController')->show(Sprint::current($project->id));
	}
}
