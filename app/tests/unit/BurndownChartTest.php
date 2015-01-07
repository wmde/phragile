<?php

use Phragile\BurndownChart;

class BurndownChartTest extends TestCase {

	private function mockWithSprint(Sprint $sprint)
	{
		return new BurndownChart(
			$sprint,
			$this->getMockBuilder('Phragile\TaskList')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Phragile\PhabricatorAPI')->disableOriginalConstructor()->getMock());
	}

	public function dateCountProvider()
	{
		return [
			['2014-01-01', '2014-12-31', 365],
			['2014-12-01', '2014-12-14', 14],
			['2014-12-28', '2015-01-2', 6],
			['2014-11-28', '2014-12-2', 5],
		];
	}

	/**
	 * @dataProvider dateCountProvider
	 */
	public function testGetDaysReturnsCorrectNumberOfDays($start, $end, $numberOfDays)
	{
		$burndown = $this->mockWithSprint(new Sprint(['sprint_start' => $start, 'sprint_end' => $end]));

		$this->assertCount(
			$numberOfDays,
			$burndown->getDays()
		);
	}

	public function dateSequenceProvider()
	{
		return [
			['2014-01-01', '2014-01-03', ['2014-01-01', '2014-01-02', '2014-01-03',]],
			['2014-01-31', '2014-02-02', ['2014-01-31', '2014-02-01', '2014-02-02',]],
			['2014-12-31', '2015-01-02', ['2014-12-31', '2015-01-01', '2015-01-02',]],
		];
	}

	/**
	 * @dataProvider dateSequenceProvider
	 */
	public function testGetDaysIncrementsCorrectly($start, $end, $all)
	{
		$burndown = $this->mockWithSprint(new Sprint(['sprint_start' => $start, 'sprint_end' => $end]));

		$this->assertEquals($burndown->getDays('Y-m-d'), $all);
	}

	private function mockWithTransactions(array $tasks, array $transactions)
	{
		$phabricatorMock = $this->getMockBuilder('Phragile\PhabricatorAPI')
			->disableOriginalConstructor()
			->getMock();
		$phabricatorMock->method('taskTransactions')->willReturn($transactions);

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
			$phabricatorMock
		);
	}

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

		$this->assertEquals(10, $burndown->closedPerDay()['2014-12-08']);
	}

	public function testClosedPerDayDetectsBeforeAndAfter()
	{
		$burndown = $this->mockWithTransactions(
			$this->tasks,
			[
				'1' => [[
					'transactionType' => 'status',
					'oldValue' => 'open',
					'newValue' => 'resolved',
					'dateCreated' => '1415664000', // Nov 11
				]],
				'2' => [[
					'transactionType' => 'status',
					'oldValue' => 'open',
					'newValue' => 'resolved',
					'dateCreated' => '1428050000', // Apr 3
				]]
			]
		);

		$closed = $burndown->closedPerDay();
		$this->assertEquals(8, $closed['before']);
		$this->assertEquals(2, $closed['after']);
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

		$closed = $burndown->closedPerDay();
		$this->assertEquals(0, $closed['2014-12-09']);
		$this->assertEquals(8, $closed['2014-12-08']);
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

		$closed = $burndown->closedPerDay();
		$this->assertEquals(0, $closed['2014-12-08']);
		$this->assertEquals(8, $closed['2014-12-09']);
	}
}
