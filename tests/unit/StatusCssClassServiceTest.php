<?php
use Phragile\StatusCssClassService;

class StatusCssClassServiceTest extends TestCase {
	/**
	 * @dataProvider statusProvider
	 */
	public function testReturnsStatusForStatusMode($status)
	{
		$service = new StatusCssClassService(false, []);
		$this->assertSame($status, $service->getCssClass($status));
	}

	/**
	 * @dataProvider columnProvider
	 */
	public function testReturnsOpenOrClosedForWorkboardMode($column, $cssClass)
	{
		$service = new StatusCssClassService(true, ['Done', 'Deployed']);
		$this->assertSame($cssClass, $service->getCssClass($column));
	}

	public function statusProvider()
	{
		return [['open'], ['resolved'], ['stalled'], ['invalid']];
	}

	public function columnProvider()
	{
		return [
			['To Do', 'open'],
			['Doing', 'open'],
			['Done', 'closed'],
			['Deployed', 'closed'],
		];
	}
}
