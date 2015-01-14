<?php

use Phragile\TaskList;
use Phragile\BurndownChart;

class SprintSnapshotsController extends BaseController {

	public function show(SprintSnapshot $snapshot)
	{
		$sprint = $snapshot->sprint;
		$currentSprint = $sprint->project->currentSprint();
		$sprintData = json_decode($snapshot->data, true);
		$taskList = new TaskList($sprintData['tasks']);
		$burndown = new BurndownChart($sprint, $taskList, $sprintData['transactions']);

		return View::make('sprint.view', compact('snapshot', 'sprint', 'currentSprint', 'taskList', 'burndown'));
	}
}
