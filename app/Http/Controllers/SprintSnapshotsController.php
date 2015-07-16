<?php

use Phragile\Factory\SprintDataFactory;

class SprintSnapshotsController extends Controller {

	public function show(SprintSnapshot $snapshot)
	{
		$factory = $this->getSprintDataFactory($snapshot);

		return View::make('sprint.view', [
			'snapshot' => $snapshot,
			'sprint' => $snapshot->sprint,
			'currentSprint' => $factory->getCurrentSprint(),
			'pieChartData' => $factory->getPieChartData(),
			'burndown' => $factory->getBurndownChart(),
			'burnup' => $factory->getBurnupChart(),
			'sprintBacklog' => $factory->getSprintBacklog()
		]);
	}

	public function exportJSON(SprintSnapshot $snapshot)
	{
		$factory = $this->getSprintDataFactory($snapshot);
		return Response::json($factory->getBurnChartData());
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

	private function getSprintDataFactory(SprintSnapshot $snapshot)
	{
		$sprintData = json_decode($snapshot->data, true);
		return new SprintDataFactory(
			$snapshot->sprint,
			$sprintData['tasks'],
			$sprintData['transactions'],
			App::make('phabricator')
		);
	}
}
