<?php
namespace Phragile\Factory;

use Phragile\BurnupChart;
use Phragile\PieChart;
use Phragile\ProjectColumnRepository;
use Phragile\PhabricatorAPI;
use Phragile\StatusCssClassService;
use Phragile\TaskList;
use Phragile\ClosedTimeByStatusFieldDispatcher;
use Phragile\ClosedTimeByWorkboardDispatcher;
use Phragile\AssigneeRepository;
use Phragile\BurndownChart;
use Phragile\ScopeLine;
use Phragile\TaskDataProcessor;
use Phragile\Task;

class SprintDataFactory {
	private $sprint = null;
	private $transactions = [];
	private $phabricatorAPI = null;

	private $taskList = null;
	private $columns = null;
	private $burndownChart = null;
	private $cssClassService = null;
	private $pieChart = null;

	public function __construct(\Sprint $sprint, array $taskRawData, array $transactions, PhabricatorAPI $phabricatorAPI)
	{
		$this->sprint = $sprint;
		$this->transactions = $transactions;
		$this->phabricatorAPI = $phabricatorAPI;

		$this->columns = new ProjectColumnRepository($this->sprint->phid, $this->transactions, $this->phabricatorAPI);
		$tasks = (new TaskDataProcessor(
			(new StatusDispatcherFactory($sprint, $this->columns, $transactions))->getStatusDispatcher(),
			['ignore_estimates' => $sprint->ignore_estimates, 'ignored_columns' => $sprint->project->getIgnoredColumns()]
		))->process($taskRawData);
		$this->taskList = new TaskList($tasks);

		$this->cssClassService = new StatusCssClassService($this->isWorkboardMode(), $this->getClosedColumns());
		$this->pieChart = new PieChart($this->taskList->getTasksPerStatus(), $this->cssClassService);
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
		return $this->pieChart->getData();
	}

	public function getStatusColors()
	{
		return $this->pieChart->getStatusColors();
	}

	public function getSprintBacklog()
	{
		$assignees = new AssigneeRepository($this->phabricatorAPI, $this->taskList->getTasks());

		return array_map(function(Task $task) use($assignees)
		{
			$task->setAssigneeName($assignees->getName($task->getAssigneePHID()) ?: '-');
			$task->setCssClass($this->cssClassService->getCssClass($task->getStatus()));

			return $task;
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
