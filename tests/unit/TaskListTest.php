<?php

use Phragile\TaskList;
use Phragile\StatusByStatusFieldDispatcher;
use Phragile\StatusByWorkboardDispatcher;
use Phragile\ProjectColumnRepository;
use Phragile\TransactionList;

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

	public function testIgnoreEstimates()
	{
		$taskList = $this->createTaskListIgnoringEstimatesWithStatusFieldDispatcher($this->tasks);

		$this->assertSame(2, $taskList->getTasksPerStatus()['resolved']['points']);
		$this->assertSame(1, $taskList->getTasksPerStatus()['open']['points']);
	}

	public function testGetTasksPerStatusWithWorkboardDispatcher()
	{
		$taskList = $this->createTaskListWithWorkboardDispatcher($this->tasks, $this->getProjectColumnTransactions());

		$this->assertSame(5, $taskList->getTasksPerStatus()['done']['points']);
		$this->assertSame(10, $taskList->getTasksPerStatus()['to do']['points']);
	}

	public function testOtherProjectTransactionsShouldBeIgnored()
	{
		$transactions = $this->getProjectColumnTransactions();
		$transactions['5'] = [[
			'transactionType' => 'projectcolumn',
			'oldValue' => [
				'columnPHIDs' => [],
				'projectPHID' => 'PHID-456', // not identical to $this->testProjectPHID and should be ignored
			],
			'newValue' => [
				'columnPHIDs' => [array_keys($this->workboardColumns)[2]],
			]
		]];
		$taskList = $this->createTaskListWithWorkboardDispatcher(
			$this->tasks,
			$transactions
		);

		$this->assertSame(10, $taskList->getTasksPerStatus()['to do']['points']);
	}

	private $testProjectPHID = 'PHID-123';

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

	private function getProjectColumnTransactions()
	{
		return [
			'1' => [[
				'transactionType' => 'projectcolumn',
				'oldValue' => [
					'columnPHIDs' => ['anyNotClosed'],
					'projectPHID' => $this->testProjectPHID,
				],
				'newValue' => [
					'columnPHIDs' => [array_keys($this->workboardColumns)[0]],
				]
			]],
			'2' => [[
				'transactionType' => 'projectcolumn',
				'oldValue' => [
					'columnPHIDs' => ['anyNotClosed'],
					'projectPHID' => $this->testProjectPHID,
				],
				'newValue' => [
					'columnPHIDs' => [array_keys($this->workboardColumns)[1]],
				]
			]],
			'3' => [[
				'transactionType' => 'projectcolumn',
				'oldValue' => [
					'columnPHIDs' => [],
					'projectPHID' => $this->testProjectPHID,
				],
				'newValue' => [
					'columnPHIDs' => [array_keys($this->workboardColumns)[2]],
				]
			]],
			'4' => [[
				'transactionType' => 'projectcolumn',
				'oldValue' => [
					'columnPHIDs' => [],
					'projectPHID' => $this->testProjectPHID,
				],
				'newValue' => [
					'columnPHIDs' => [array_keys($this->workboardColumns)[2]],
				]
			]],
			'5' => []
		];
	}

	private $workboardColumns = [
		'PHID-123abc' => 'wontfix',
		'PHID-321cba' => 'done',
		'PHID-abc123' => 'to do',
	];

	private function createTaskListWithStatusFieldDispatcher(array $tasks)
	{
		return new TaskList($tasks, new StatusByStatusFieldDispatcher('PHID-REVIEW123'));
	}

	private function createTaskListIgnoringEstimatesWithStatusFieldDispatcher(array $tasks)
	{
		return new TaskList($tasks, new StatusByStatusFieldDispatcher('PHID-REVIEW123'), true);
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
			$this->testProjectPHID,
			new TransactionList($transactions),
			new ProjectColumnRepository($this->testProjectPHID, $transactions, $phabricatorAPI),
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
