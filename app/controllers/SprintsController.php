<?php

class SprintsController extends BaseController {

	public function create($projectSlug)
	{
		$project = Project::where('slug', $projectSlug)->first();
		return View::make('sprint.create', compact('project'));
	}
}
