<?php

use Phragile\BurndownChart;
use Phragile\ClosedTimeByStatusFieldDispatcher;
use Phragile\ClosedTimeByWorkboardDispatcher;
use Phragile\ClosedTimeDispatcher;
use Phragile\ColumnChangeTransaction;
use Phragile\MergedIntoTransaction;
use Phragile\StatusChangeTransaction;
use Phragile\Task;

class BurndownChartTest extends TestCase {

	private function mockWithTransactions(array $tasks, array $transactions)
	{
		return $this->mockWithTransactionsAndClosedTimeDispatcher($tasks, $transactions, new ClosedTimeByStatusFieldDispatcher());
	}

	private function mockWithTransactionsAndClosedTimeDispatcher(
		array $tasks, array $transactions, ClosedTimeDispatcher $dispatcher
	)
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
			'points' => 8
		],
		'2' => [
			'id' => 2,
			'closed' => true,
			'points' => 2
		]
	];

	/**
	 * @before
	 */
	public function initDummyTasks()
	{
		$this->tasks = array_map(function($taskData)
		{
			return new Task(array_merge($taskData, [
				'title' => 'A Task',
				'priority' => 'Normal',
				'status' => 'Open',
				'assigneePHID' => null,
			]));
		}, $this->tasks);
	}

	private $closedColumnPHIDs = ['123abc', 'abc123'];

	public function testClosedPerDayAddsStoryPoints()
	{
		$burndown = $this->mockWithTransactions(
			$this->tasks,
			[
				'1' => [
					new StatusChangeTransaction(
						'1418040000', // Dec 8
						'open',
						'resolved'
					)
				],
				'2' => [
					new StatusChangeTransaction(
						'1418050000',
						'open',
						'resolved'
					)
				]
			]
		);

		$this->assertSame(10, $burndown->getPointsClosedPerDay()['2014-12-08']);
	}

	public function testClosedPerDayDetectsBefore()
	{
		$burndown = $this->mockWithTransactions(
			$this->tasks,
			['1' => [new StatusChangeTransaction(
				'1415664000', // Nov 11
				'open',
				'resolved'
			)]]
		);

		$this->assertSame(8, $burndown->getPointsClosedBeforeSprint());
	}

	public function testClosedPerDayIgnoresClosedToClosedTransaction()
	{
		$burndown = $this->mockWithTransactions(
			['1' => $this->tasks['1']],
			[
				'1' => [
					new StatusChangeTransaction(
						'1418040000', // Dec 8
						'open',
						'resolved'
					),
					new StatusChangeTransaction(
						'1418130000', // Dec 9
						'resolved',
						'invalid'
					)
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
					new StatusChangeTransaction(
						'1418040000', // Dec 8
						'open',
						'resolved'
					),
					new StatusChangeTransaction(
						'1418050000',
						'resolved',
						'open'
					),
					new StatusChangeTransaction(
						'1418130000', // Dec 9
						'open',
						'resolved'
					)
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
				'1' => [new ColumnChangeTransaction(
					'1418040000', // Dec 8
					$this->testProjectPHID,
					'anyNotClosed',
					$this->closedColumnPHIDs[1]
				)],
				'2' => [new \Phragile\ColumnChangeTransaction(
					'1418050000', // Dec 8
					$this->testProjectPHID,
					'anyNotClosed',
					$this->closedColumnPHIDs[0]
				)]
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
					new ColumnChangeTransaction(
						DateTime::createFromFormat('d.m.Y H:i:s', '08.12.2014 10:00:00')->format('U'),
						$this->testProjectPHID,
						'anyNotClosed',
						$this->closedColumnPHIDs[1]
					),
					new ColumnChangeTransaction(
						DateTime::createFromFormat('d.m.Y H:i:s', '08.12.2014 12:00:00')->format('U'),
						$this->testProjectPHID,
						$this->closedColumnPHIDs[1],
						'anyNotClosed'
					),
					new ColumnChangeTransaction(
						DateTime::createFromFormat('d.m.Y H:i:s', '09.12.2014 10:00:00')->format('U'),
						$this->testProjectPHID,
						'anyNotClosed',
						$this->closedColumnPHIDs[1]
					),
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
				'1' => [new StatusChangeTransaction(
					'1418040000', // Dec 8
					'open',
					'resolved'
				)],
				'2' => [new StatusChangeTransaction(
					'1418050000', // Dec 8
					'open',
					'resolved'
				)]
			]
		);

		$this->assertSame(0, $burndown->getPointsClosedPerDay()['2014-12-08']);
	}

	public function testOpenTaskTransactionsAreIgnored()
	{
		$burndown = $this->mockWithTransactions(
			['500' => new Task([
				'title' => 'A Task',
				'priority' => 'Normal',
				'id' => 500,
				'status' => 'Open',
				'closed' => false,
				'assigneePHID' => null,
				'points' => 5,
			])],
			['500' => [new StatusChangeTransaction(
				'1415664000', // Nov 11
				'open',
				'resolved'
			)]]
		);

		$this->assertNull($burndown->getPointsClosedBeforeSprint());
	}

	public function testClosedPerDayDetectsMergedTasks()
	{
		$burndown = $this->mockWithTransactions(
			$this->tasks,
			[
				'1' => [new MergedIntoTransaction(
					'1418040000' // Dec 8
				)]
			]
		);

		$this->assertSame(8, $burndown->getPointsClosedPerDay()['2014-12-08']);
	}
}
