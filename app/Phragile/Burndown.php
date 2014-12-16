<?php
namespace Phragile;

class Burndown {
	// TODO: ideally these should come from a cached call to maniphest.querystatuses
	private static $STATUS_OPEN = ['stalled', 'open'];

	public function __construct(\Sprint $sprint, TaskList $tasks, PhabricatorAPI $phabricator)
	{
		$this->sprint = $sprint;
		$this->tasks = $tasks;
		$this->phabricator = $phabricator;
		$this->days = $this->days();
	}

	public function days()
	{
		$days = [];

		for ($day = strtotime($this->sprint->sprint_start);
		     $day <= strtotime($this->sprint->sprint_end);
		     $day += 60*60*24)
		{
			$days[] = date('Y-m-d', $day);
		}

		return $days;
	}

	public function getDays($format = 'M j')
	{
		return array_map(function($day) use($format)
		{
			return date($format, strtotime($day));
		}, $this->days);
	}

	public function closedPerDay()
	{
		$closedPerDay = array_fill_keys(array_merge($this->days, ['before', 'after']), 0);

		foreach ($this->closedTaskTimes() as $id => $time)
		{
			$task = $this->tasks->findTaskByID($id);
			$formattedDate = date('Y-m-d', $time);

			if (isset($closedPerDay[$formattedDate]))
			{
				$closedPerDay[$formattedDate] += $task['story_points'];
			} elseif ($formattedDate < $this->sprint->sprint_start)
			{
				$closedPerDay['before'] += $task['story_points'];
			} else
			{
				$closedPerDay['after'] += $task['story_points'];
			}
		}

		return $closedPerDay;
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
