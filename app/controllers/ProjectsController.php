<?php

class ProjectsController extends BaseController {

	public function show($slug)
	{
		$project = Project::where('slug', $slug)->first();
		return View::make('project.view', compact('project'));
	}
}
