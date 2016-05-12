<?php

namespace Phragile\ActionHandler;

use Phragile\PhabricatorAPI;
use Phragile\SettingsAwareTransactionFilter;
use Phragile\TaskDataFetcher;
use Phragile\TransactionRawDataProcessor;
use Phragile\TransactionLoader;
use Sprint;
use Phragile\Factory\SprintDataFactory;

class SprintLiveDataActionHandler {

	private $phabricatorAPI;

	public function __construct(PhabricatorAPI $phabricatorAPI)
	{
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
			'statusColors' => $factory->getStatusColors(),
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
		$tasks = (new TaskDataFetcher($this->phabricatorAPI))->fetchProjectTasks($sprint->phid);
		$taskIDs = array_map(function($task)
		{
			return $task['id'];
		}, $tasks);
		$transactionDataProcessor = new TransactionRawDataProcessor();
		$transactionLoader = new TransactionLoader(
			new SettingsAwareTransactionFilter($sprint->project->workboard_mode),
			$this->phabricatorAPI
		);

		return new SprintDataFactory(
			$sprint,
			$tasks,
			$transactionDataProcessor->process($transactionLoader->load($taskIDs)),
			$this->phabricatorAPI
		);
	}
}
