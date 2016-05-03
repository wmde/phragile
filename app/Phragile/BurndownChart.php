<?php
namespace Phragile;

class BurndownChart {
	private $pointsClosedBeforeSprint = null;
	private $pointsClosedPerDay = null;
	private $sprint = null;
	private $tasks = null;
	private $transactions = null;
	private $closedTimeDispatcher = null;

	/**
	 * @param \Sprint $sprint
	 * @param TaskList $tasks
	 * @param Transaction[] $transactions - an associative array that maps an array of transactions to task IDs
	 * @param ClosedTimeDispatcher $closedTimeDispatcher - an associative array that maps an array of transactions to task IDs
	 */
	public function __construct(
		\Sprint $sprint, TaskList $tasks, array $transactions, ClosedTimeDispatcher $closedTimeDispatcher
	)
	{
		$this->sprint = $sprint;
		$this->tasks = $tasks;
		$this->transactions = $this->filterOutOpenTaskTransactions($transactions);
		$this->closedTimeDispatcher = $closedTimeDispatcher;
	}

	private function calculatePointsClosedPerDay()
	{
		$dateFormat = 'Y-m-d';
		$sprintDuration = $this->sprint->getFormattedDays($dateFormat);
		$this->pointsClosedPerDay = array_fill_keys($sprintDuration, 0);

		foreach ($this->getClosedTaskTimes() as $id => $time)
		{
			$taskClosedDay = date($dateFormat, $time);

			if (in_array($taskClosedDay, $sprintDuration))
			{
				$this->pointsClosedPerDay[$taskClosedDay] += $this->tasks->findTaskByID($id)->getPoints();
			} elseif ($taskClosedDay < $this->sprint->sprint_start)
			{
				$this->pointsClosedBeforeSprint += $this->tasks->findTaskByID($id)->getPoints();
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
			function($time, Transaction $transaction)
			{
				if ($this->closedTimeDispatcher->isClosingTransaction($transaction))
				{
					//return $transaction['dateCreated'];
					return $transaction->getTimestamp();
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

	private function filterOutOpenTaskTransactions(array $transactions)
	{
		$closedTaskTransactions = [];
		foreach ($transactions as $taskID => $taskTransactions)
		{
			if ($this->tasks->findTaskByID($taskID) && $this->tasks->findTaskByID($taskID)->isClosed())
			{
				$closedTaskTransactions[$taskID] = $taskTransactions;
			}
		}

		return $closedTaskTransactions;
	}
}
