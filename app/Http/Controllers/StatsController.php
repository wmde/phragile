<?php

class StatsController extends Controller {

	public function index()
	{
		return View::make('stats.index')->with([
			'projectCount' => Project::count(),
			'sprintCount' => Sprint::count(),
			'sprintsPerProject' => $this->getSprintsPerProject(),
		]);
	}

	private function getSprintsPerProject() {
		return Project::all()->map(function($project) {
			return [$project->title, $project->sprints->count()];
		});
	}
}
