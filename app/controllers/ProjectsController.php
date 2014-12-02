<?php

class ProjectsController extends BaseController {

	public function show(Project $project)
	{
		$currentSprint = Sprint::where('sprint_start', '<=', 'CURRENT_DATE')->take(1)->first();

		return View::make('project.view', compact('project', 'currentSprint'));
	}
}
