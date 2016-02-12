<?php
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

		foreach($chart->getData() as $data)
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

		$this->assertSame($chart->getStatusColors()['Done'], 'rgb(' . implode(',', PieChart::$GREEN) . ')');
	}

	public function testHasDefaultColorFor1OpenTask()
	{
		$chart = new PieChart(
			['To Do' => []],
			new StatusCssClassService(true, ['Done'])
		);

		$this->assertSame($chart->getStatusColors()['To Do'], 'rgb(' . implode(',', PieChart::$ORANGE) . ')');
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
}
