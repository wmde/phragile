<?php
namespace Phragile\Factory;

use Phragile\BurnupChart;
use Phragile\ProjectColumnRepository;
use Phragile\PhabricatorAPI;
use Phragile\StatusCssClassService;
use Phragile\TaskList;
use Phragile\ClosedTimeByStatusFieldDispatcher;
use Phragile\ClosedTimeByWorkboardDispatcher;
use Phragile\AssigneeRepository;
use Phragile\BurndownChart;
use Phragile\ScopeLine;

class SprintDataFactory {
	private $sprint = null;
	private $tasks = [];
	private $transactions = [];
	private $phabricatorAPI = null;

	private $taskList = null;
	private $columns = null;
	private $burndownChart = null;
	private $cssClassService = null;

	public function __construct(\Sprint $sprint, array $tasks, array $transactions, PhabricatorAPI $phabricatorAPI)
	{
		$this->sprint = $sprint;
		$this->tasks = $tasks;
		$this->transactions = $transactions;
		$this->phabricatorAPI = $phabricatorAPI;

		$this->columns = new ProjectColumnRepository($this->sprint->phid, $this->transactions, $this->phabricatorAPI);
		$this->taskList = new TaskList(
			$tasks,
			(new StatusDispatcherFactory($sprint, $this->columns, $transactions))->getStatusDispatcher(),
			['ignore_estimates' => $sprint->ignore_estimates, 'ignored_columns' => $sprint->project->getIgnoredColumns()]
		);

		$this->cssClassService = new StatusCssClassService($this->isWorkboardMode(), $this->getClosedColumns());
		$this->burndownChart = $this->generateBurndownData();
	}

	public function getCurrentSprint()
	{
		return $this->sprint->project->currentSprint();
	}

	public function getBurnChartData()
	{
		$pointsClosedBeforeSprint = $this->burndownChart->getPointsClosedBeforeSprint();
		return [
			'pointsClosedBeforeSprint' => isset($pointsClosedBeforeSprint) ? $pointsClosedBeforeSprint : 0,
			'sprint' => $this->getBurnupData()
		];
	}

	public function getPieChartData()
	{
		$pieChartData = [];

		foreach ($this->taskList->getTasksPerStatus() as $status => $task)
		{
			$pieChartData[$status] = array_merge($task, ['cssClass' => $this->cssClassService->getCssClass($status)]);
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
				'cssClass' => $this->cssClassService->getCssClass($task['status']),
			]);
		}, $this->taskList->getTasks());
	}

	private function generateBurndownData()
	{
		return new BurndownChart(
			$this->sprint,
			$this->taskList,
			$this->transactions,
			$this->isWorkboardMode()
				? new ClosedTimeByWorkboardDispatcher($this->sprint->phid, $this->getClosedColumnPHIDs())
				: new ClosedTimeByStatusFieldDispatcher()
		);
	}

	private function getBurnupData()
	{
		$burnupChart = new BurnupChart(
			$this->burndownChart->getPointsClosedPerDay(),
			new ScopeLine(
				$this->sprint->sprintSnapshots->all() ?: [],
				$this->taskList->getTasksPerStatus()['total']['points'],
				$this->sprint->getFormattedDays('Y-m-d')
			)
		);
		return $burnupChart->getData();
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
}
