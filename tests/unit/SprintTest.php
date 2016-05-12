<?php

namespace Phragile\Tests;

class SprintTest extends TestCase {
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
		$sprint = new \Sprint(['sprint_start' => $start, 'sprint_end' => $end]);

		$this->assertCount(
			$numberOfDays,
			$sprint->getFormattedDays()
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
		$sprint = new \Sprint(['sprint_start' => $start, 'sprint_end' => $end]);

		$this->assertSame($sprint->getFormattedDays('Y-m-d'), $all);
	}
}
