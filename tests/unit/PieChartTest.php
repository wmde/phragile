<?php

namespace Phragile\Tests;

use Phragile\PieChart;
use \Phragile\StatusCssClassService;

class PieChartTest extends TestCase {
	public function tasksPerStatusProvider()
	{
		return [[
			[
				'open' => ['tasks' => 2, 'points' => 5],
				'doing' => ['tasks' => 3, 'points' => 8],
			],
		]];
	}

	/**
	 * @dataProvider tasksPerStatusProvider
	 */
	public function testAddsClassesToStatuses($tasksPerStatus)
	{
		$chart = new PieChart($tasksPerStatus, new StatusCssClassService(false));

		foreach ($chart->getData() as $data)
		{
			$this->assertArrayHasKey('cssClass', $data);
		}
	}

	public function testHasDefaultColorFor1DoneTask()
	{
		$chart = new PieChart(
			['Done' => []],
			new StatusCssClassService(true, ['Done'])
		);

		$this->assertSame($chart->getStatusColors()['closed done'], 'rgb(' . implode(',', PieChart::$GREEN) . ')');
	}

	public function testHasDefaultColorFor1OpenTask()
	{
		$chart = new PieChart(
			['To Do' => []],
			new StatusCssClassService(true, ['Done'])
		);

		$this->assertSame($chart->getStatusColors()['open to-do'], 'rgb(' . implode(',', PieChart::$ORANGE) . ')');
	}

	public function testHasDifferentColorShadesForEachType()
	{
		$chart = new PieChart(
			['Done' => [], 'Deployed' => [], 'Invalid' => [], 'Wontfix' => []],
			new StatusCssClassService(true, ['Done', 'Deployed', 'Invalid', 'Wontfix'])
		);

		$this->assertSame(
			count($chart->getStatusColors()),
			count(array_unique(array_values($chart->getStatusColors()), SORT_REGULAR))
		);
	}

	public function testHasCssClassAsKeys()
	{
		$chart = new PieChart(
			['To Do' => [], 'Deployed' => [], 'To Be Discussed' => [], 'Wontfix' => []],
			new StatusCssClassService(true, ['Done', 'Deployed', 'Invalid', 'Wontfix'])
		);

		$colorMap = $chart->getStatusColors();
		$this->assertArrayHasKey('open to-do', $colorMap);
		$this->assertArrayHasKey('open to-be-discussed', $colorMap);
		$this->assertArrayHasKey('closed deployed', $colorMap);
		$this->assertArrayHasKey('closed wontfix', $colorMap);
	}

	public function testOrdersByCssClass()
	{
		$chart = new PieChart(
			[
				'Done' => ['points' => 10],
				'Doing' => ['points' => 11],
				'Blocked By Others' => ['points' => 12],
				'Deployed' => ['points' => 13]
			],
			new StatusCssClassService(true, ['Done', 'Deployed'])
		);

		$colorMap = array_values($chart->getData());
		$this->assertSame($colorMap[0]['cssClass'], 'closed deployed');
		$this->assertSame($colorMap[1]['cssClass'], 'closed done');
		$this->assertSame($colorMap[2]['cssClass'], 'open blocked-by-others');
		$this->assertSame($colorMap[3]['cssClass'], 'open doing');
	}
}
