<?php

class SprintsController extends BaseController {

	public function create(Project $project)
	{
		return View::make('sprint.create', compact('project'));
	}
}
