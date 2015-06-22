<?php
namespace Phragile;

class BurnupChart {
	private $data = null;

	public function __construct(array $closedPerDay, ScopeLine $scopeLine)
	{
		$this->data = $this->calculateBurnupData($scopeLine->getData(), $burndownChart->getPointsClosedPerDay());
	}

	private function calculateBurnupData(array $scopeLine, array $closedPerDay)
	{
		$burnupData = [];
		$points = 0;

		foreach ($scopeLine as $day => $scope)
		{
			$points += $closedPerDay[$day];
			$burnupData[$day] = [
				'scope' => $scope,
				'points' => $points,
			];
		}

		return $burnupData;
	}

	public function getData()
	{
		return $this->data;
	}
}
