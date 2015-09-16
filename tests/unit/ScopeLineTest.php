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
		$snapshot1 = $this->newSnapshot('2015-01-01 00:00:00', ['total_points' => 42]);
		$snapshot2 = $this->newSnapshot('2015-01-01 02:00:00', ['total_points' => 41]);
		$scopeLine = new ScopeLine([$snapshot1, $snapshot2], 43, ['2015-01-01']);

		$data = $scopeLine->getData();
		$this->assertSame($data['2015-01-01'], 41);
	}

	public function testShouldFillTotalPointsBackwardsFromFirstSnapshot()
	{
		$duration = ['2015-01-01', '2015-01-02'];
		$snapshot = $this->newSnapshot($duration[1], ['total_points' => 42]);
		$scopeLine = new ScopeLine([$snapshot], 40, $duration);

		$data = $scopeLine->getData();
		$this->assertSame($data[$duration[0]], 42);
		$this->assertSame($data[$duration[1]], 42);
	}

	public function newSnapshot($date, $fields, $sprint = null)
	{
		$snapshot = new SprintSnapshot($fields);
		$snapshot->sprint = $sprint ?: new Sprint(['ignore_estimates' => false, 'title' => 'wat']);
		$snapshot->setCreatedAt($date);

		return $snapshot;
	}

	public function testShouldConsiderCurrentNumberOfStoryPoints()
	{
		$daySeconds = 3600 * 24;
		$dateFormat = 'Y-m-d';
		$currentTime = time();
		$duration = [
			date($dateFormat, $currentTime - 2 * $daySeconds),
			date($dateFormat, $currentTime - $daySeconds),
			date($dateFormat, $currentTime),
			date($dateFormat, $currentTime + $daySeconds),
		];
		$snapshot = $this->newSnapshot(date($dateFormat, $currentTime - $daySeconds), ['total_points' => 42]);
		$scopeLine = new ScopeLine([$snapshot], 40, $duration);

		$data = $scopeLine->getData();
		$this->assertSame($data[$duration[0]], 42);
		$this->assertSame($data[$duration[1]], 42);
		$this->assertSame($data[$duration[2]], 40);
		$this->assertSame($data[$duration[3]], 40);
	}

	public function testShouldUseTaskCountBasedOnSettings()
	{
		$sprint = new Sprint(['ignore_estimates' => true, 'title' => 'sup']);
		$duration = ['2015-01-01', '2015-01-02'];
		$s1 = $this->newSnapshot($duration[0], ['total_points' => 5, 'task_count' => 2], $sprint);
		$s2 = $this->newSnapshot($duration[1], ['total_points' => 8, 'task_count' => 3], $sprint);

		$scopeLine = new ScopeLine([$s1, $s2], 3, $duration);
		$data = $scopeLine->getData();
		$this->assertSame($data[$duration[0]], 2);
		$this->assertSame($data[$duration[1]], 3);
	}
}
