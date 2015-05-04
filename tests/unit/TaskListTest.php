<?php

use Phragile\TaskList;
use Phragile\StatusByStatusFieldDispatcher;

class TaskListTest extends TestCase {

	public function __construct()
	{
		$this->addDummyDataToTasks();
	}

	public function testGetTasksPerStatus()
	{
		$taskList = $this->createTaskListWithStatusFieldDispatcher($this->tasks);

		$this->assertSame(13, $taskList->getTasksPerStatus()['resolved']['points']);
		$this->assertSame(8, $taskList->getTasksPerStatus()['open']['points']);
	}

	private $tasks = [
		[
			'status' => 'open',
			'auxiliary' => ['std:maniphest:WMDE:story_points' => 8]
		],
		[
			'status' => 'resolved',
			'auxiliary' => ['std:maniphest:WMDE:story_points' => 5]
		],
		[
			'status' => 'resolved',
			'auxiliary' => ['std:maniphest:WMDE:story_points' => 8]
		]
	];

	private function createTaskListWithStatusFieldDispatcher(array $tasks)
	{
		return new TaskList($tasks, new StatusByStatusFieldDispatcher());
	}

	private function addDummyDataToTasks()
	{
		$this->tasks = array_map(function($task)
		{
			return array_merge($task, [
				'title' => 'a task',
				'priority' => 'low',
				'isClosed' => false,
				'projectPHIDs' => ['x'],
				'ownerPHID' => null,
				'id' => 1,
			]);
		}, $this->tasks);
	}
}
