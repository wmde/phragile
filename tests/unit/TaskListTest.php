<?php

use Phragile\TaskList;
use Phragile\StatusByStatusFieldDispatcher;
use Phragile\StatusByWorkboardDispatcher;
use Phragile\ProjectColumnRepository;
use Phragile\TransactionList;

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

	private $testSprint = null;
	/**
	 * @before
	 */
	public function initTestSprint()
	{
		$this->testSprint = new Sprint([
			'phid' => 'PHID-123',
		]);
		$this->testSprint->project = new Project([
			'closed_columns' => 'done',
		]);
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
				'projectPHID' => 'PHID-456', // not identical to $this->testSprint->phid and should be ignored
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

	public function testIgnoreToDoColumn()
	{
		$taskList = $this->createTaskListWithWorkboardDispatcher($this->tasks, $this->getProjectColumnTransactions(), []);
		$taskListIgnore = $this->createTaskListWithWorkboardDispatcher($this->tasks, $this->getProjectColumnTransactions(), ['to do']);

		$this->assertFalse(isset($taskListIgnore->getTasksPerStatus()['to do']['points']));
		$this->assertSame(10, $taskList->getTasksPerStatus()['to do']['points']);

		$this->assertSame(30, $taskList->getTasksPerStatus()['total']['points']);
		$this->assertSame(20, $taskListIgnore->getTasksPerStatus()['total']['points']);
	}

	public function testDefaultColumn()
	{
		$taskList = $this->createTaskListWithWorkboardDispatcher($this->tasks, $this->getProjectColumnTransactions(), []);
		$this->assertSame(7, $taskList->getTasksPerStatus()['Backlog']['points']);

		$this->testSprint->project->default_column = 'Incoming';
		$taskList = $this->createTaskListWithWorkboardDispatcher($this->tasks, $this->getProjectColumnTransactions(), []);
		$this->assertFalse(isset($taskList->getTasksPerStatus()['Backlog']['points']));
		$this->assertSame(7, $taskList->getTasksPerStatus()['Incoming']['points']);
	}

	private function getProjectColumnTransactions()
	{
		return [
			'1' => [[
				'transactionType' => 'projectcolumn',
				'oldValue' => [
					'columnPHIDs' => ['anyNotClosed'],
					'projectPHID' => $this->testSprint->phid,
				],
				'newValue' => [
					'columnPHIDs' => [array_keys($this->workboardColumns)[0]],
				]
			]],
			'2' => [[
				'transactionType' => 'projectcolumn',
				'oldValue' => [
					'columnPHIDs' => ['anyNotClosed'],
					'projectPHID' => $this->testSprint->phid,
				],
				'newValue' => [
					'columnPHIDs' => [array_keys($this->workboardColumns)[1]],
				]
			]],
			'3' => [[
				'transactionType' => 'projectcolumn',
				'oldValue' => [
					'columnPHIDs' => [],
					'projectPHID' => $this->testSprint->phid,
				],
				'newValue' => [
					'columnPHIDs' => [array_keys($this->workboardColumns)[2]],
				]
			]],
			'4' => [[
				'transactionType' => 'projectcolumn',
				'oldValue' => [
					'columnPHIDs' => [],
					'projectPHID' => $this->testSprint->phid,
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
		return new TaskList($tasks, new StatusByStatusFieldDispatcher('PHID-REVIEW123'), ['ignore_estimates' => false, 'ignored_columns' => []]);
	}

	private function createTaskListIgnoringEstimatesWithStatusFieldDispatcher(array $tasks)
	{
		return new TaskList($tasks, new StatusByStatusFieldDispatcher('PHID-REVIEW123'), ['ignore_estimates' => true, 'ignored_columns' => []]);
	}

	private function createTaskListWithWorkboardDispatcher(array $tasks, $transactions, array $ignored_columns = [])
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

		return new TaskList(
			$tasks,
			new StatusByWorkboardDispatcher(
				$this->testSprint,
				new TransactionList($transactions),
				new ProjectColumnRepository($this->testSprint->phid, $transactions, $phabricatorAPI)
			),
			['ignore_estimates' => false, 'ignored_columns' => $ignored_columns]
		);
	}
}
