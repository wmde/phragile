<?php

class StatsController extends Controller {

	public function index()
	{
		return View::make('stats.index')->with([
			'sprintCount' => Sprint::count(),
			'projects' => Project::with('sprints'),
		]);
	}
}
