<?php
use Phragile\ScopeLine;

class ScopeLineTest extends TestCase {
	public function dateRangeProvider()
	{
		return [
			[['2015-01-01', '2015-01-02', '2015-01-03',]],
			[['2015-01-31', '2015-02-01', '2015-02-02',]],
			[['2015-12-31', '2016-01-01', '2016-01-02',]],
		];
	}

	/**
	 * @dataProvider dateRangeProvider
	 */
	public function testNumberOfPointsShouldNotChangeWithNoSnapshots($dateRange)
	{
		$numberOfPoints = 42;
		$scopeLine = new ScopeLine([], $numberOfPoints, $dateRange);

		foreach ($scopeLine->getData() as $date => $points)
			$this->assertSame($points, $numberOfPoints);
	}
}
