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
		$tasks = $this->phabricatorAPI->queryTasksByProject($sprint->phid);
		$factory = new SprintDataFactory(
			$sprint,
			$tasks,
			$this->phabricatorAPI->getTaskTransactions(array_map(function($task)
			{
				return $task['id'];
			}, $tasks)),
			$this->phabricatorAPI
		);


		return [
			'sprint' => $sprint,
			'currentSprint' => $factory->getCurrentSprint(),
			'burndown' => $factory->getBurndownChart(),
			'pieChartData' => $factory->getPieChartData(),
			'sprintBacklog' => $factory->getSprintBacklog()
		];
	}

}
