<?php

use Phragile\BurndownChart;

class BurndownChartTest extends TestCase {

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
}
