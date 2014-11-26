<?php

class ProjectsController extends BaseController {

	public function show($slug)
	{
		$project = Project::where('slug', $slug)->first();
		return $project->title;
	}
}
