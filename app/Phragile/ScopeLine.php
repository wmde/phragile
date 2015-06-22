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
		$this->snapshots = $this->groupSnapshotsByDay($snapshots);
		$this->pointsNumber = $pointsNumber;
		$this->data = $this->calculateScopeLine(array_fill_keys($dateRange, $pointsNumber));
	}

	private function calculateScopeLine(array $days)
	{
		foreach ($days as $day => $points)
		{
			if (isset($this->snapshots[$day])) $days[$day] = $this->snapshots[$day]->total_points;
		}

		return $days;
	}

	private function groupSnapshotsByDay(array $snapshots)
	{
		$snapshotsMap = [];

		foreach ($snapshots as $snapshot)
		{
			$dateCreatedAt = date('Y-m-d', strtotime($snapshot->created_at));
			$snapshotsMap[$dateCreatedAt] = $snapshot;
		}

		return $snapshotsMap;
	}

	public function getData()
	{
		return $this->data;
	}
}
