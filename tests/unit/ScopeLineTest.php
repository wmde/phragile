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

	public function testShouldAlwaysUseLastSnapshot()
	{
		$snapshot1 = new SprintSnapshot([
			'total_points' => 42,
		]);
		$snapshot1->setCreatedAt('2015-01-01 00:00:00');
		$snapshot2 = new SprintSnapshot([
			'total_points' => 41,
		]);
		$snapshot2->setCreatedAt('2015-01-01 02:00:00');
		$scopeLine = new ScopeLine([$snapshot1, $snapshot2], 43, ['2015-01-01']);

		$data = $scopeLine->getData();
		$this->assertSame($data['2015-01-01'], 41);
	}

	public function testShouldFillTotalPointsBackwardsFromFirstSnapshot()
	{
		$snapshot = new SprintSnapshot([
			'total_points' => 42,
		]);
		$snapshot->setCreatedAt('2015-01-02 00:00:00');
		$duration = ['2015-01-01', '2015-01-02'];
		$scopeLine = new ScopeLine([$snapshot], 40, $duration);

		$data = $scopeLine->getData();
		$this->assertSame($data[$duration[0]], 42);
		$this->assertSame($data[$duration[1]], 42);
	}
}
