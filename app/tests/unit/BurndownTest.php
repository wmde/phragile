<?php

use Phragile\Burndown;

class BurndownTest extends TestCase {

	private function mockWithSprint(Sprint $sprint)
	{
		return new Burndown(
			$sprint,
			$this->getMockBuilder('Phragile\TaskList')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Phragile\PhabricatorAPI')->disableOriginalConstructor()->getMock());
	}

	public function dateCountProvider()
	{
		return [
			['2014-01-01', '2014-12-31', 365],
			['2014-12-01', '2014-12-14', 14],
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

	private $tasks = [
		'7' => [
			'closed' => true,
			'id' => '7',
			'story_points' => 3
		],
		'42' => [
			'closed' => true,
			'id' => '42',
			'story_points' => 13
		],
		'5' => [ // ignored, because closed => false
			'closed' => false,
			'id' => '5',
			'story_points' => 3
		],
		'1' => [
			'closed' => true,
			'id' => '1',
			'story_points' => 3
		],
		'2' => [
			'closed' => false,
			'id' => '2',
			'story_points' => 13
		],
		'3' => [
			'closed' => false,
			'id' => '3',
			'story_points' => 5
		],
	];

	private $transactions = [
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
			'dateCreated' => '1414664000', // Oct 30
		]],
		'3' => [[
			'transactionType' => 'status',
			'oldValue' => 'open',
			'newValue' => 'resolved',
			'dateCreated' => '1419033600', // Oct 30
		]],
		'7' => [
			[
				'transactionType' => 'status',
				'oldValue' => 'open',
				'newValue' => 'resolved',
				'dateCreated' => '1418040000', // Dec 8
			],
			['transactionType' => 'priority'], // ignored because it's not a status transaction
			[ // should be ignored because it's a status change from closed to closed
				'transactionType' => 'status',
				'oldValue' => 'resolved',
				'newValue' => 'wontfix',
				'dateCreated' => '1428050000', // Apr 3
			]
		],
		'42' => [
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
				'dateCreated' => '1418050000', // Dec 8
			],
			[ // should override the previous one
				'transactionType' => 'status',
				'oldValue' => 'open',
				'newValue' => 'wontfix',
				'dateCreated' => '1418130000', // Dec 9
			]
		],
	];

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

		return new Burndown(
			new Sprint(['sprint_start' => '2014-12-01', 'sprint_end' => '2014-12-14']),
			$taskListMock,
			$phabricatorMock
		);
	}

	public function testClosedPerDay()
	{
		$burndown = $this->mockWithTransactions($this->tasks, $this->transactions);

		$closed = $burndown->closedPerDay();
		$this->assertEquals(16, $closed['before']); // task 1 + 2
		$this->assertEquals(5, $closed['after']); // task 3
		$this->assertEquals(13, $closed['2014-12-09']); // task 42
		$this->assertEquals(3, $closed['2014-12-08']); // task 7
	}
}
