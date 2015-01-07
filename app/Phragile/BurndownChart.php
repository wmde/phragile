<?php
namespace Phragile;

class BurndownChart {
	// TODO: ideally these should come from a cached call to maniphest.querystatuses
	private static $STATUS_OPEN = ['stalled', 'open'];

	public function __construct(\Sprint $sprint, TaskList $tasks, PhabricatorAPI $phabricator)
	{
		$this->sprint = $sprint;
		$this->tasks = $tasks;
		$this->phabricator = $phabricator;
		$this->days = $this->daysInSprint();
	}

	private function daysInSprint()
	{
		$days = [];

		for ($day = strtotime($this->sprint->sprint_start);
		     $day <= strtotime($this->sprint->sprint_end);
		     $day += 60*60*24)
		{
			$days[] = $day;
		}

		return $days;
	}

	public function getDays($format = 'M j')
	{
		return array_map(function($day) use($format)
		{
			return date($format, $day);
		}, $this->days);
	}

	public function closedPerDay()
	{
		$closedPerDay = array_fill_keys(array_merge($this->getDays('Y-m-d'), ['before', 'after']), 0);

		foreach ($this->closedTaskTimes() as $id => $time)
		{
			$closedPerDay[$this->closedTimeInSprint($time)] += $this->tasks->findTaskByID($id)['story_points'];
		}

		return $closedPerDay;
	}

	private function closedTimeInSprint($time)
	{
		$format = 'Y-m-d';
		$formattedDate = date($format, $time);

		if (in_array($formattedDate, $this->getDays($format)))
		{
			return $formattedDate;
		} elseif ($formattedDate < $this->sprint->sprint_start)
		{
			return 'before';
		} else
		{
			return 'after';
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
}
