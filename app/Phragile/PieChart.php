<?php
namespace Phragile;

class PieChart {
	private $data = [];
	private $cssClassService = null;
	public static $GREEN = [170, 204, 30];
	public static $ORANGE = [255, 180, 38];

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

	private function getStatusesByCssClass($cssClass)
	{
		return array_keys(array_filter($this->getData(), function($status) use($cssClass)
		{
			return strpos($status['cssClass'], $cssClass) === 0;
		}));
	}

	private function getColors($statuses, $base)
	{
		$colorMap = [];
		$colors = (new ColorGenerator())->generate(count($statuses), $base);

		for ($i = 0; $i < count($statuses); $i++)
		{
			$colorMap[$statuses[$i]] = $colors[$i];
		}

		return $colorMap;
	}

	/**
	 * @return array - Colors mapped to statuses
	 */
	public function getStatusColors()
	{
		return $this->mapToCssClasses(array_merge(
			$this->getColors($this->getStatusesByCssClass('closed'), PieChart::$GREEN),
			$this->getColors($this->getStatusesByCssClass('open'), PieChart::$ORANGE)
		));
	}

	private function mapToCssClasses(array $statusMap)
	{
		$cssClassMap = [];

		foreach ($statusMap as $status => $color)
		{
			$cssClassMap[$this->cssClassService->getCssClass($status)] = $color;
		}

		return $cssClassMap;
	}
}
