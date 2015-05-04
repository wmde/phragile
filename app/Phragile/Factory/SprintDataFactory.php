<?php
namespace Phragile\Factory;

use Phragile\ProjectColumnRepository;
use Phragile\PhabricatorAPI;
use Phragile\TaskList;
use Phragile\StatusByStatusFieldDispatcher;
use Phragile\StatusByWorkboardDispatcher;
use Phragile\ClosedTimeByStatusFieldDispatcher;
use Phragile\ClosedTimeByWorkboardDispatcher;
use Phragile\AssigneeRepository;
use Phragile\BurndownChart;

class SprintDataFactory {
	private $sprint = null;
	private $tasks = [];
	private $transactions = [];
	private $phabricatorAPI = null;

	private $taskList = null;
	private $columns = null;

	public function __construct(\Sprint $sprint, array $tasks, array $transactions, PhabricatorAPI $phabricatorAPI)
	{
		$this->sprint = $sprint;
		$this->tasks = $tasks;
		$this->transactions = $transactions;
		$this->phabricatorAPI = $phabricatorAPI;

		$this->columns = $this->fetchProjectColumns();
		$this->taskList = new TaskList(
			$tasks,
			$sprint->project->workboard_mode
				? new StatusByWorkboardDispatcher($this->transactions, $this->columns, $this->getClosedColumns())
				: new StatusByStatusFieldDispatcher(env('REVIEW_TAG_PHID'))
		);
	}

	public function getCurrentSprint()
	{
		return $this->sprint->project->currentSprint();
	}

	public function getBurndownChart()
	{
		return new BurndownChart(
			$this->sprint,
			$this->taskList,
			$this->transactions,
			$this->isWorkboardMode()
				? new ClosedTimeByWorkboardDispatcher($this->getClosedColumnPHIDs())
				: new ClosedTimeByStatusFieldDispatcher()
		);
	}

	public function getPieChartData()
	{
		$pieChartData = [];

		foreach ($this->taskList->getTasksPerStatus() as $status => $task)
		{
			$pieChartData[$status] = array_merge($task, ['cssClass' => $this->getStatusCssClass($status)]);
		}

		return $pieChartData;
	}

	public function getSprintBacklog()
	{
		$assignees = new AssigneeRepository($this->phabricatorAPI, $this->tasks);

		return array_map(function($task) use($assignees)
		{
			return array_merge($task, [
				'assignee' => $assignees->getName($task['assignee']) ?: '-',
				'cssClass' => $this->getStatusCssClass($task['status']),
			]);
		}, $this->taskList->getTasks());
	}

	private function getStatusCssClass($status)
	{
		if ($this->sprint->project->workboard_mode && $status !== 'total')
			return in_array($status, $this->getClosedColumns()) ? 'closed' : 'open';
		else return $status;
	}

	private function isWorkboardMode()
	{
		return $this->sprint->project->workboard_mode;
	}

	private function getClosedColumnPHIDs()
	{
		return array_map(function($columnName)
		{
			return $this->columns->getColumnPHID($columnName);
		}, $this->getClosedColumns());
	}

	private function getClosedColumns()
	{
		return $this->sprint->project->getClosedColumns();
	}

	private function fetchProjectColumns()
	{
		return new ProjectColumnRepository($this->transactions, $this->phabricatorAPI);
	}
}
