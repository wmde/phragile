<?php

namespace Phragile\Presentation;

class TaskList {
	private $tasks = null;

	/**
	 * @param Task[] $tasks
	 */
	public function __construct(array $tasks)
	{
		$this->tasks = $tasks;
	}

	/**
	 * @return Task[]
	 */
	public function getTasks()
	{
		return $this->tasks;
	}

	/**
	 * @return array[] Associative array of task number and story points per status
	 */
	public function getTasksPerStatus()
	{
		return array_reduce($this->tasks, function($acc, Task $task)
		{
			$acc['total']['tasks'] += 1;
			$acc['total']['points'] += $task->getPoints();

			if (isset($acc[$task->getStatus()]))
			{
				$acc[$task->getStatus()]['tasks'] += 1;
				$acc[$task->getStatus()]['points'] += $task->getPoints();
			} else
			{
				$acc[$task->getStatus()] = [
					'tasks' => 1,
					'points' => $task->getPoints()
				];
			}

			return $acc;
		}, ['total' => ['points' => 0, 'tasks' => 0]]);
	}

	/**
	 * @param int $id
	 * @return Task|null Task data
	 */
	public function findTaskByID($id)
	{
		foreach ($this->tasks as $task)
		{
			if ($task->getId() == $id)
			{
				return $task;
			}
		}
		return null;
	}

	/**
	 * @return int[] List of IDs of closed tasks
	 */
	public function getClosedTaskIDs()
	{
		return array_map(
			function(Task $task)
			{
				return $task['id'];
			},
			array_filter(
				$this->tasks,
				function($task)
				{
					return $task['closed'];
				}
			)
		);
	}
}
