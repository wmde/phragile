<?php
namespace Phragile;

class BurndownChart {
	// TODO: ideally these should come from a cached call to maniphest.querystatuses
	private static $STATUS_OPEN = ['stalled', 'open'];

	private $pointsClosedBeforeSprint = null;
	private $pointsClosedPerDay = null;
	private $sprint = null;
	private $tasks = null;
	private $transactions = null;

	public function __construct(\Sprint $sprint, TaskList $tasks, array $transactions)
	{
		$this->sprint = $sprint;
		$this->tasks = $tasks;
		$this->transactions = $transactions;
	}

	private function calculatePointsClosedPerDay()
	{
		$dateFormat = 'Y-m-d';
		$sprintDuration = $this->sprint->formatDays($dateFormat);
		$this->pointsClosedPerDay = array_fill_keys($sprintDuration, 0);

		foreach ($this->getClosedTaskTimes() as $id => $time)
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

	private function getClosedTaskTimes()
	{
		return array_map(function($transactions)
		{
			return $this->findLastStatusChangeToClosed($transactions);
		}, $this->transactions);
	}

	private function findLastStatusChangeToClosed(array $transactions)
	{
		return array_reduce(
			$transactions,
			function($time, $transaction)
			{
				if ($this->statusChangedFromOpen($transaction))
				{
					return $transaction['dateCreated'];
				} else
				{
					return $time;
				}
			}
		);
	}

	private function statusChangedFromOpen(array $transaction)
	{
		return $transaction['transactionType'] === 'status'
			&& in_array($transaction['oldValue'], self::$STATUS_OPEN)
			&& !in_array($transaction['newValue'], self::$STATUS_OPEN);
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
