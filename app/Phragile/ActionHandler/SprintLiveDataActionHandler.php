<?php

namespace Phragile\ActionHandler;

use Phragile\PhabricatorAPI;
use Sprint;
use Phragile\Factory\SprintDataFactory;

class SprintLiveDataActionHandler {

	private $phabricatorAPI;

	public function __construct(PhabricatorAPI $phabricatorAPI) {
		$this->phabricatorAPI = $phabricatorAPI;
	}

	public function getViewData(Sprint $sprint)
	{
		$factory = $this->getSprintDataFactory($sprint);

		return [
			'sprint' => $sprint,
			'currentSprint' => $factory->getCurrentSprint(),
			'burndown' => $factory->getBurndownChart(),
			'burnup' => $factory->getBurnupChart(),
			'pieChartData' => $factory->getPieChartData(),
			'sprintBacklog' => $factory->getSprintBacklog()
		];
	}

	public function getExportData(Sprint $sprint)
	{
		$factory = $this->getSprintDataFactory($sprint);
		$pointsClosedBeforeSprint = $factory->getBurndownChart()->getPointsClosedBeforeSprint();
		return [
			'pointsClosedBeforeSprint' => isset($pointsClosedBeforeSprint) ? $pointsClosedBeforeSprint : 0,
			'sprint' => $factory->getBurnupChart()->getData()
		];
	}

	private function getSprintDataFactory(Sprint $sprint)
	{
		$tasks = $this->phabricatorAPI->queryTasksByProject($sprint->phid);
		return new SprintDataFactory(
			$sprint,
			$tasks,
			$this->phabricatorAPI->getTaskTransactions(array_map(function($task)
			{
				return $task['id'];
			}, $tasks)),
			$this->phabricatorAPI
		);
	}
}
