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
}
