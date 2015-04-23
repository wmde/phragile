<?php

namespace Phragile\ActionHandler;

use Phragile\AssigneeRepository;
use Phragile\BurndownChart;
use Phragile\ClosedTimeDispatcherFactory;
use Phragile\PhabricatorAPI;
use Phragile\ProjectColumnRepository;
use Phragile\StatusByStatusFieldDispatcher;
use Phragile\StatusByWorkboardDispatcher;
use Phragile\TaskList;
use Sprint;

class SprintLiveDataActionHandler {

	private $phabricatorAPI;

	public function __construct(PhabricatorAPI $phabricatorAPI) {
		$this->phabricatorAPI = $phabricatorAPI;
	}

	public function getViewData(Sprint $sprint)
	{
		$currentSprint = $sprint->project->currentSprint();
		$tasks = $this->phabricatorAPI->queryTasksByProject($sprint->phid);
		$transactions = $this->phabricatorAPI->getTaskTransactions(array_map(function($task)
		{
			return $task['id'];
		}, $tasks));

		$columns = new ProjectColumnRepository($transactions, $this->phabricatorAPI);
		$taskList = new TaskList(
			$tasks,
			$sprint->project->workboard_mode
				? new StatusByWorkboardDispatcher($transactions, $columns, $sprint->project->getClosedColumns())
				: new StatusByStatusFieldDispatcher()
		);
		$assignees = new AssigneeRepository($this->phabricatorAPI, $tasks);
		$burndown = new BurndownChart(
			$sprint,
			$taskList,
			$transactions,
			(new ClosedTimeDispatcherFactory($sprint->project->workboard_mode))->createInstance()
		);

		return compact('sprint', 'currentSprint', 'taskList', 'burndown', 'assignees');
	}

}
