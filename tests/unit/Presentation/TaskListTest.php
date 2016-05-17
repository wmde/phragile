<?php

namespace Phragile\Tests\Presentation;

use Phragile\Domain\Task as DomainTask;
use Phragile\Presentation\Task;
use Phragile\Presentation\TaskList;
use Phragile\Tests\TestCase;

class TaskListTest extends TestCase {

	private $pointsAndStatuses = [
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

	private function getTasks()
	{
		$i = 0;
		return array_map(function(array $pointsAndStatus) use(&$i)
		{
			return new Task(
				new DomainTask([
						'title' => 'a task',
						'priority' => 'low',
						'status' => 'open',
						'projectPHIDs' => ['x'],
						'assigneePHID' => null,
						'id' => ++$i,
						'points' => 2,
				]),
				$pointsAndStatus['status'],
				Task::OPEN_TASK,
				$pointsAndStatus['points']
			);
		}, $this->pointsAndStatuses);
	}


	public function testGetTasksPerStatus()
	{
		$taskList = new TaskList($this->getTasks());

		$this->assertSame(13, $taskList->getTasksPerStatus()['resolved']['points']);
		$this->assertSame(8, $taskList->getTasksPerStatus()['open']['points']);
	}
}
