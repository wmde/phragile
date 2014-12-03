<?php

use Phragile\TaskList;

class ProjectsController extends BaseController {

	public function show(Project $project)
	{
		$currentSprint = Sprint::where('sprint_start', '<=', date('Y-m-d'))
		                         ->orderBy('sprint_start', 'desc')
		                         ->first();
		$taskList = new TaskList(App::make('phabricator'), $currentSprint->phid);

		return View::make('project.view', compact('project', 'currentSprint', 'taskList'));
	}
}
