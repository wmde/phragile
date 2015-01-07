<?php
namespace Phragile;

class BurndownChart {
	// TODO: ideally these should come from a cached call to maniphest.querystatuses
	private static $STATUS_OPEN = ['stalled', 'open'];

	private $pointsClosedBeforeSprint = null;
	private $pointsClosedPerDay = null;

	public function __construct(\Sprint $sprint, TaskList $tasks, PhabricatorAPI $phabricator)
	{
		$this->sprint = $sprint;
		$this->tasks = $tasks;
		$this->phabricator = $phabricator;
	}

	private function calculatePointsClosedPerDay()
	{
		$dateFormat = 'Y-m-d';
		$sprintDuration = $this->sprint->formatDays($dateFormat);
		$this->pointsClosedPerDay = array_fill_keys($sprintDuration, 0);

		foreach ($this->closedTaskTimes() as $id => $time)
		{
			$taskClosedDay = date($dateFormat, $time);

			if (in_array($taskClosedDay, $sprintDuration))
			{
				$this->pointsClosedPerDay[$taskClosedDay] += $this->tasks->findTaskByID($id)['story_points'];
			} elseif ($taskClosedDay < $this->sprint->sprint_start)
			{
				$this->pointsClosedBeforeSprint += $this->tasks->findTaskByID($id)['story_points'];
			}
		}
	}

	private function closedTaskIDs()
	{
		return array_map(function($task)
		{
			return $task['id'];
		}, array_filter($this->tasks->getTasks(), function($task)
			{
				return $task['closed'];
			})
		);
	}

	private function closedTaskTimes()
	{
		$taskTransactions = $this->phabricator->taskTransactions($this->closedTaskIDs());

		return array_map(function($transactions)
		{
			return $this->findLastClosed($transactions);
		}, $taskTransactions);
	}

	private function findLastClosed(array $transactions)
	{
		return array_reduce(
			$transactions,
			function($time, $transaction)
			{
				if ($transaction['transactionType'] === 'status'
				    && in_array($transaction['oldValue'], self::$STATUS_OPEN)
				    && !in_array($transaction['newValue'], self::$STATUS_OPEN))
				{
					return $transaction['dateCreated'];
				} else
				{
					return $time;
				}
			}
		);
	}

	/**
	 * @return int
	 */
	public function getPointsClosedBeforeSprint()
	{
		if ($this->pointsClosedBeforeSprint === null)
		{
			$this->calculatePointsClosedPerDay();
		}

		return $this->pointsClosedBeforeSprint;
	}

	/**
	 * Returns an associative array that maps the number of closed points to a day of the sprint.
	 *
	 * @return array
	 */
	public function getPointsClosedPerDay()
	{
		if ($this->pointsClosedPerDay === null)
		{
			$this->calculatePointsClosedPerDay();
		}

		return $this->pointsClosedPerDay;
	}
}
