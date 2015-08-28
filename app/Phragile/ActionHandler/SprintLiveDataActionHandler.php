<?php

namespace Phragile\ActionHandler;

use Phragile\PhabricatorAPI;
use Phragile\TransactionLoader;
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
			'burnChartData' => $factory->getBurnChartData(),
			'pieChartData' => $factory->getPieChartData(),
			'sprintBacklog' => $factory->getSprintBacklog()
		];
	}

	public function getExportData(Sprint $sprint)
	{
		$factory = $this->getSprintDataFactory($sprint);
		return $factory->getBurnChartData();
	}

	private function getSprintDataFactory(Sprint $sprint)
	{
		$tasks = $this->phabricatorAPI->queryTasksByProject($sprint->phid);
		$taskIDs = array_map(function($task)
		{
			return $task['id'];
		}, $tasks);
		$transactionLoader = new TransactionLoader();

		return new SprintDataFactory(
			$sprint,
			$tasks,
			$transactionLoader->load($taskIDs, $this->phabricatorAPI),
			$this->phabricatorAPI
		);
	}
}
