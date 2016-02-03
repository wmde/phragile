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
}
