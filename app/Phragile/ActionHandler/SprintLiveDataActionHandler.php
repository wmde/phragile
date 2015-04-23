<?php

namespace Phragile\ActionHandler;

use Phragile\AssigneeRepository;
use Phragile\BurndownChart;
use Phragile\ClosedTimeByStatusFieldDispatcher;
use Phragile\ClosedTimeByWorkboardDispatcher;
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
		$closedColumns = $sprint->project->getClosedColumns();
		$taskList = new TaskList(
			$tasks,
			$sprint->project->workboard_mode
				? new StatusByWorkboardDispatcher($transactions, $columns, $closedColumns)
				: new StatusByStatusFieldDispatcher()
		);
		$assignees = new AssigneeRepository($this->phabricatorAPI, $tasks);
		$closedColumnPHIDs = array_map(function($columnName) use($columns)
		{
			return $columns->getColumnPHID($columnName);
		}, $closedColumns);

		$burndown = new BurndownChart(
			$sprint,
			$taskList,
			$transactions,
			$sprint->project->workboard_mode
				? new ClosedTimeByWorkboardDispatcher($closedColumnPHIDs)
				: new ClosedTimeByStatusFieldDispatcher()
		);

		return compact('sprint', 'currentSprint', 'taskList', 'burndown', 'assignees');
	}

}
