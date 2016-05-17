<?php
namespace Phragile\Factory;

use Phragile\BurnupChart;
use Phragile\PieChart;
use Phragile\ProjectColumnRepository;
use Phragile\PhabricatorAPI;
use Phragile\StatusCssClassService;
use Phragile\Presentation\TaskList;
use Phragile\ClosedTimeByStatusFieldDispatcher;
use Phragile\ClosedTimeByWorkboardDispatcher;
use Phragile\AssigneeRepository;
use Phragile\BurndownChart;
use Phragile\ScopeLine;
use Phragile\TaskPresenter;
use Phragile\Domain\Task as DomainTask;
use Phragile\Domain\Transaction;
use Phragile\Presentation\Task as PresentationTask;

class SprintDataFactory {
	private $sprint = null;
	/**
	 * @var \Phragile\Domain\Transaction[]
	 */
	private $transactions = [];
	private $phabricatorAPI = null;

	private $taskList = null;
	private $columns = null;
	private $burndownChart = null;
	private $cssClassService = null;
	private $pieChart = null;

	/**
	 * SprintDataFactory constructor.
	 * @param \Sprint $sprint
	 * @param DomainTask[] $tasks
	 * @param Transaction[] $transactions
	 * @param PhabricatorAPI $phabricatorAPI
	 */
	public function __construct(\Sprint $sprint, array $tasks, array $transactions, PhabricatorAPI $phabricatorAPI)
	{
		$this->sprint = $sprint;
		$this->transactions = $transactions;
		$this->phabricatorAPI = $phabricatorAPI;

		$this->columns = new ProjectColumnRepository($this->sprint->phid, $this->transactions, $this->phabricatorAPI);
		$presentationTask = (new TaskPresenter(
			(new StatusDispatcherFactory($sprint, $this->columns, $transactions))->getStatusDispatcher(),
			['ignore_estimates' => $sprint->ignore_estimates, 'ignored_columns' => $sprint->project->getIgnoredColumns()]
		))->render($tasks);
		$this->taskList = new TaskList($presentationTask);

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

		// TODO: would it make sense to move getting assignee name and css class to TaskRenderer?
		return array_map(function(PresentationTask $task) use($assignees)
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
