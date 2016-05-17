<?php

namespace Phragile\Tests;

use Phragile\Task;
use Phragile\TaskList;

class TaskListTest extends TestCase {

	private $tasks = [
		[
			'status' => 'open',
			'points' => 8
		],
		[
			'status' => 'resolved',
			'points' => 5
		],
		[
			'status' => 'resolved',
			'points' => 8
		],
		[
			'status' => 'wontfix',
			'points' => 2
		],
		[
			'status' => 'wontfix',
			'points' => 7
		]
	];
	/**
	 * @before
	 */
	public function addDummyDataToTasks()
	{
		$i = 0;
		$this->tasks = array_map(function($task) use(&$i)
		{
			return new Task(array_merge($task, [
				'title' => 'a task',
				'priority' => 'low',
				'closed' => false,
				'projectPHIDs' => ['x'],
				'assigneePHID' => null,
				'id' => ++$i,
				'auxiliary' => [env('MANIPHEST_STORY_POINTS_FIELD') => $task['points']],
			]));
		}, $this->tasks);
	}


	public function testGetTasksPerStatus()
	{
		$taskList = new TaskList($this->tasks);

		$this->assertSame(13, $taskList->getTasksPerStatus()['resolved']['points']);
		$this->assertSame(8, $taskList->getTasksPerStatus()['open']['points']);
	}
}
