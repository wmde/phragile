<?php

use Phragile\TaskList;
use Phragile\StatusByStatusFieldDispatcher;
use Phragile\StatusByWorkboardDispatcher;
use Phragile\ProjectColumnRepository;

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

	public function testGetTasksPerStatusWithWorkboardDispatcher()
	{
		$taskList = $this->createTaskListWithWorkboardDispatcher($this->tasks, $this->getProjectColumnTransactions());

		$this->assertSame(5, $taskList->getTasksPerStatus()['done']['points']);
		$this->assertSame(10, $taskList->getTasksPerStatus()['to do']['points']);
	}

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
		]
	];

	private function getProjectColumnTransactions()
	{
		return [
			'1' => [[
				'transactionType' => 'projectcolumn',
				'oldValue' => [
					'columnPHIDs' => ['anyNotClosed'],
				],
				'newValue' => [
					'columnPHIDs' => [array_keys($this->workboardColumns)[0]],
				]
			]],
			'2' => [[
				'transactionType' => 'projectcolumn',
				'oldValue' => [
					'columnPHIDs' => ['anyNotClosed'],
				],
				'newValue' => [
					'columnPHIDs' => [array_keys($this->workboardColumns)[1]],
				]
			]],
			'3' => [[
				'transactionType' => 'projectcolumn',
				'oldValue' => [
					'columnPHIDs' => [],
				],
				'newValue' => [
					'columnPHIDs' => [array_keys($this->workboardColumns)[2]],
				]
			]],
			'4' => [[
				'transactionType' => 'projectcolumn',
				'oldValue' => [
					'columnPHIDs' => [],
				],
				'newValue' => [
					'columnPHIDs' => [array_keys($this->workboardColumns)[2]],
				]
			]]
		];
	}

	private $workboardColumns = [
		'PHID-123abc' => 'wontfix',
		'PHID-321cba' => 'done',
		'PHID-abc123' => 'to do',
	];

	private function createTaskListWithStatusFieldDispatcher(array $tasks)
	{
		return new TaskList($tasks, new StatusByStatusFieldDispatcher());
	}

	private function createTaskListWithWorkboardDispatcher(array $tasks, $transactions)
	{
		$phabricatorAPI = $this->getMockBuilder('Phragile\PhabricatorAPI')
			->disableOriginalConstructor()
			->getMock();

		$phabricatorAPI->method('queryPHIDs')->will($this->returnCallback(function()
		{
			return array_map(function($column)
			{
				return ['name' => $column];
			}, $this->workboardColumns);
		}));

		return new TaskList($tasks, new StatusByWorkboardDispatcher(
			$transactions,
			new ProjectColumnRepository($transactions, $phabricatorAPI),
			array_values($this->workboardColumns)
		));
	}

	private function addDummyDataToTasks()
	{
		$i = 0;
		$this->tasks = array_map(function($task) use(&$i)
		{
			return array_merge($task, [
				'title' => 'a task',
				'priority' => 'low',
				'isClosed' => false,
				'projectPHIDs' => ['x'],
				'ownerPHID' => null,
				'id' => ++$i,
				'auxiliary' => [env('MANIPHEST_STORY_POINTS_FIELD') => $task['points']],
			]);
		}, $this->tasks);
	}
}
