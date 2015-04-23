<?php

use Phragile\TaskList;
use Phragile\AssigneeRepository;
use Phragile\BurndownChart;
use Phragile\StatusByStatusFieldDispatcher;
use Phragile\StatusByWorkboardDispatcher;
use Phragile\ClosedTimeDispatcherFactory;
use Phragile\ProjectColumnRepository;

class SprintSnapshotsController extends Controller {

	public function show(SprintSnapshot $snapshot)
	{
		$sprint = $snapshot->sprint;
		$currentSprint = $sprint->project->currentSprint();
		$sprintData = json_decode($snapshot->data, true);
		$columns = new ProjectColumnRepository($sprintData['transactions'], App::make('phabricator'));
		$taskList = new TaskList(
			$sprintData['tasks'],
			$sprint->project->workboard_mode
				? new StatusByWorkboardDispatcher($sprintData['transactions'], $columns, $sprint->project->getClosedColumns())
				: new StatusByStatusFieldDispatcher()
		);
		$assignees = new AssigneeRepository(App::make('phabricator'), $sprintData['tasks']);
		$burndown = new BurndownChart(
			$sprint,
			$taskList,
			$sprintData['transactions'],
			(new ClosedTimeDispatcherFactory($sprint->project->workboard_mode))->createInstance()
		);

		return View::make('sprint.view', compact('snapshot', 'sprint', 'currentSprint', 'taskList', 'burndown', 'assignees'));
	}

	public function store(Sprint $sprint)
	{
		$snapshot = $sprint->createSnapshot();

		if ($snapshot->exists)
		{
			Flash::success("Successfully created a snapshot for \"$sprint->title\"");
			return Redirect::route('snapshot_path', $snapshot->id);
		} else
		{
			Flash::error("The snapshot could not be created. Please try again");
			return Redirect::route('sprint_live_path', $sprint->phabricator_id);
		}
	}

	public function delete(SprintSnapshot $snapshot)
	{
		if ($snapshot->delete())
		{
			Flash::success('The snapshot was deleted.');
			return Redirect::route('sprint_path', ['sprint' => $snapshot->sprint->phabricator_id]);
		} else
		{
			Flash::error('The snapshot could not be deleted. Please try again.');
			return Redirect::back();
		}
	}
}
