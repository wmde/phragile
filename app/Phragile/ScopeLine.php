<?php
namespace Phragile;

class ScopeLine {
	private $snapshots = [];
	private $pointsNumber;
	private $dateRange = [];
	private $data = [];

	/**
	 * @param array $snapshots - List of snapshots for the sprint's scope line
	 * @param int $pointsNumber - Number of story points in the sprint at the moment
	 * @param array $dateRange - Array of dates from sprint start to end
	 */
	public function __construct(array $snapshots, $pointsNumber, array $dateRange)
	{
		$this->snapshots = $this->groupSnapshotsByDay($snapshots);
		$this->pointsNumber = $pointsNumber;
		$this->dateRange = $dateRange;
		$this->data = $this->calculateScopeLine(array_fill_keys($dateRange, $pointsNumber));
	}

	private function calculateScopeLine(array $days)
	{
		$currentPoints = $this->findInitialPointsNumber();
		$today = date('Y-m-d');

		foreach ($days as $day => $points)
		{
			if ($day >= $today)
			{
				$days[$day] = $this->pointsNumber;
			} elseif (isset($this->snapshots[$day]))
			{
				$days[$day] = $this->snapshots[$day]->total_points;
				$currentPoints = $this->snapshots[$day]->total_points;
			} else
			{
				$days[$day] = $currentPoints;
			}
		}

		return $days;
	}

	private function findInitialPointsNumber()
	{
		foreach ($this->dateRange as $date)
		{
			if (isset($this->snapshots[$date])) return $this->snapshots[$date]->total_points;
		}

		return $this->pointsNumber;
	}

	private function groupSnapshotsByDay(array $snapshots)
	{
		$snapshotsMap = [];

		foreach ($snapshots as $snapshot)
		{
			$dateCreatedAt = date('Y-m-d', strtotime($snapshot->getCreatedAt()));
			$snapshotsMap[$dateCreatedAt] = $snapshot;
		}

		return $snapshotsMap;
	}

	public function getData()
	{
		return $this->data;
	}
}
