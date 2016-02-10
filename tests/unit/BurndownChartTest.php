<?php

use Phragile\BurndownChart;
use Phragile\ClosedTimeByStatusFieldDispatcher;
use Phragile\ClosedTimeByWorkboardDispatcher;
use Phragile\ClosedTimeDispatcher;

class BurndownChartTest extends TestCase {

	private function mockWithTransactions(array $tasks, array $transactions)
	{
		return $this->mockWithTransactionsAndClosedTimeDispatcher($tasks, $transactions, new ClosedTimeByStatusFieldDispatcher());
	}

	private function mockWithTransactionsAndClosedTimeDispatcher(array $tasks, array $transactions, ClosedTimeDispatcher $dispatcher)
	{
		$taskListMock = $this->getMockBuilder('Phragile\TaskList')
			->disableOriginalConstructor()
			->getMock();
		$taskListMock->method('getTasks')->willReturn($tasks);
		$taskListMock->method('findTaskByID')->will($this->returnCallback(function($id) use($tasks)
		{
			return $tasks[$id];
		}));

		return new BurndownChart(
			new Sprint(['sprint_start' => '2014-12-01', 'sprint_end' => '2014-12-14']),
			$taskListMock,
			$transactions,
			$dispatcher
		);
	}

	private function mockWithTransactionsInWorkboardMode(array $tasks, array $transactions)
	{
		return $this->mockWithTransactionsAndClosedTimeDispatcher(
			$tasks,
			$transactions,
			new ClosedTimeByWorkboardDispatcher($this->testProjectPHID, $this->closedColumnPHIDs)
		);
	}

	private $testProjectPHID = 'PHID-123';

	private $tasks = [
		'1' => [
			'id' => 1,
			'closed' => true,
			'story_points' => 8
		],
		'2' => [
			'id' => 2,
			'closed' => true,
			'story_points' => 2
		]
	];

	private $closedColumnPHIDs = ['123abc', 'abc123'];

	public function testClosedPerDayAddsStoryPoints()
	{
		$burndown = $this->mockWithTransactions(
			$this->tasks,
			[
				'1' => [[
					'transactionType' => 'status',
					'oldValue' => 'open',
					'newValue' => 'resolved',
					'dateCreated' => '1418040000', // Dec 8
				]],
				'2' => [[
					'transactionType' => 'status',
					'oldValue' => 'open',
					'newValue' => 'resolved',
					'dateCreated' => '1418050000', // Dec 8
				]]
			]
		);

		$this->assertSame(10, $burndown->getPointsClosedPerDay()['2014-12-08']);
	}

	public function testClosedPerDayDetectsBefore()
	{
		$burndown = $this->mockWithTransactions(
			$this->tasks,
			['1' => [[
				'transactionType' => 'status',
				'oldValue' => 'open',
				'newValue' => 'resolved',
				'dateCreated' => '1415664000', // Nov 11
			]]]
		);

		$this->assertSame(8, $burndown->getPointsClosedBeforeSprint());
	}

	public function testClosedPerDayIgnoresClosedToClosedTransaction()
	{
		$burndown = $this->mockWithTransactions(
			['1' => $this->tasks['1']],
			[
				'1' => [
					[
						'transactionType' => 'status',
						'oldValue' => 'open',
						'newValue' => 'resolved',
						'dateCreated' => '1418040000', // Dec 8
					],
					[
						'transactionType' => 'status',
						'oldValue' => 'resolved',
						'newValue' => 'invalid',
						'dateCreated' => '1418130000', // Dec 9
					]
				]
			]
		);

		$closed = $burndown->getPointsClosedPerDay();
		$this->assertSame(0, $closed['2014-12-09']);
		$this->assertSame(8, $closed['2014-12-08']);
	}

	public function testClosedPerDayOverridesTimeWhenClosedReopenedAndClosedAgain()
	{
		$burndown = $this->mockWithTransactions(
			['1' => $this->tasks['1']],
			[
				'1' => [
					[
						'transactionType' => 'status',
						'oldValue' => 'open',
						'newValue' => 'resolved',
						'dateCreated' => '1418040000', // Dec 8
					],
					[
						'transactionType' => 'status',
						'oldValue' => 'resolved',
						'newValue' => 'open',
						'dateCreated' => '1418050000',
					],
					[
						'transactionType' => 'status',
						'oldValue' => 'open',
						'newValue' => 'resolved',
						'dateCreated' => '1418130000', // Dec 9
					]
				]
			]
		);

		$closed = $burndown->getPointsClosedPerDay();
		$this->assertSame(0, $closed['2014-12-08']);
		$this->assertSame(8, $closed['2014-12-09']);
	}

	public function testClosedPerDayIgnoresStatusChangeInWorkboardMode()
	{
		$burndown = $this->mockWithTransactionsInWorkboardMode(
			$this->tasks,
			[
				'1' => [[
					'transactionType' => 'projectcolumn',
					'oldValue' => [
						'columnPHIDs' => ['anyNotClosed'],
						'projectPHID' => $this->testProjectPHID,
					],
					'newValue' => [
						'columnPHIDs' => [$this->closedColumnPHIDs[1]],
					],
					'dateCreated' => '1418040000', // Dec 8
				]],
				'2' => [[
					'transactionType' => 'projectcolumn',
					'oldValue' => [
						'columnPHIDs' => ['anyNotClosed'],
						'projectPHID' => $this->testProjectPHID,
					],
					'newValue' => [
						'columnPHIDs' => [$this->closedColumnPHIDs[0]],
					],
					'dateCreated' => '1418050000', // Dec 8
				]]
			]
		);

		$this->assertSame(10, $burndown->getPointsClosedPerDay()['2014-12-08']);
	}

	public function testClosedPerDayConsidersMostRecentColumnChangeInWorkboardMode()
	{
		$burndown = $this->mockWithTransactionsInWorkboardMode(
			$this->tasks,
			[
				'1' => [
					[
						'transactionType' => 'projectcolumn',
						'oldValue' => [
							'columnPHIDs' => ['anyNotClosed'],
							'projectPHID' => $this->testProjectPHID,
						],
						'newValue' => [
							'columnPHIDs' => [$this->closedColumnPHIDs[1]],
							'projectPHID' => $this->testProjectPHID,
						],
						'dateCreated' => DateTime::createFromFormat('d.m.Y H:i:s', '08.12.2014 10:00:00')->format('U'),
					],
					[
						'transactionType' => 'projectcolumn',
						'oldValue' => [
							'columnPHIDs' => [$this->closedColumnPHIDs[1]],
							'projectPHID' => $this->testProjectPHID,
						],
						'newValue' => [
							'columnPHIDs' => ['anyNotClosed'],
							'projectPHID' => $this->testProjectPHID,
						],
						'dateCreated' => DateTime::createFromFormat('d.m.Y H:i:s', '08.12.2014 12:00:00')->format('U'),
					],
					[
						'transactionType' => 'projectcolumn',
						'oldValue' => [
							'columnPHIDs' => ['anyNotClosed'],
							'projectPHID' => $this->testProjectPHID,
						],
						'newValue' => [
							'columnPHIDs' => [$this->closedColumnPHIDs[1]],
							'projectPHID' => $this->testProjectPHID,
						],
						'dateCreated' => DateTime::createFromFormat('d.m.Y H:i:s', '09.12.2014 10:00:00')->format('U'),
					],
				],
			]
		);

		$this->assertSame(0, $burndown->getPointsClosedPerDay()['2014-12-08']);
		$this->assertSame(8, $burndown->getPointsClosedPerDay()['2014-12-09']);
	}

	public function testClosedPerDayAddsStoryPointsInWorkboardMode()
	{
		$burndown = $this->mockWithTransactionsInWorkboardMode(
			$this->tasks,
			[
				'1' => [[
					'transactionType' => 'status',
					'oldValue' => 'open',
					'newValue' => 'resolved',
					'dateCreated' => '1418040000', // Dec 8
				]],
				'2' => [[
					'transactionType' => 'status',
					'oldValue' => 'open',
					'newValue' => 'resolved',
					'dateCreated' => '1418050000', // Dec 8
				]]
			]
		);

		$this->assertSame(0, $burndown->getPointsClosedPerDay()['2014-12-08']);
	}

	public function testOpenTaskTransactionsAreIgnored()
	{
		$burndown = $this->mockWithTransactions(
			['500' => [
				'id' => 500,
				'closed' => false,
				'story_points' => 5,
			]],
			['500' => [[ // this transaction's task is not closed and should be ignored
				'transactionType' => 'status',
				'oldValue' => 'open',
				'newValue' => 'resolved',
				'dateCreated' => '1415664000', // Nov 11
			]]]
		);

		$this->assertNull($burndown->getPointsClosedBeforeSprint());
	}

	public function testClosedPerDayDetectsMergedTasks()
	{
		$burndown = $this->mockWithTransactions(
			$this->tasks,
			[
				'1' => [[
					        'transactionType' => 'mergedinto',
					        'dateCreated' => '1418040000', // Dec 8
				        ]],
			]
		);

		$this->assertSame(8, $burndown->getPointsClosedPerDay()['2014-12-08']);
	}
}
