<?php
namespace Phragile;

class ScopeLine {
	private $snapshots = [];
	private $pointsNumber;
	private $data = [];

	/**
	 * @param array $snapshots - List of snapshots for the sprint's scope line
	 * @param int $pointsNumber - Number of story points in the sprint at the moment
	 * @param array $dateRange - Array of dates from sprint start to end
	 */
	public function __construct(array $snapshots, $pointsNumber, array $dateRange)
	{
		$this->snapshots = $snapshots;
		$this->pointsNumber = $pointsNumber;
		$this->data = array_fill_keys($dateRange, $pointsNumber);
	}

	public function getData()
	{
		return $this->data;
	}
}
