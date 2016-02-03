<?php
namespace Phragile;

class PieChart {
	private $data = [];
	private $cssClassService = null;

	/**
	 * @param array $data
	 * @param StatusCssClassService $cssClassService
	 */
	public function __construct(array $data, StatusCssClassService $cssClassService)
	{
		$this->data = $data;
		$this->cssClassService = $cssClassService;
	}

	/**
	 * @return array
	 */
	public function getData()
	{
		$pieChartData = [];

		foreach ($this->data as $status => $task)
		{
			$pieChartData[$status] = array_merge($task, ['cssClass' => $this->cssClassService->getCssClass($status)]);
		}

		return $pieChartData;
	}
}
