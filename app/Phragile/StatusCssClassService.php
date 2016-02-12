<?php
namespace Phragile;

class StatusCssClassService {
	private $workboardMode = false;
	private $closedColumns = [];

	/**
	 * @param boolean $workboardMode
	 * @param array $closedColumns
	 */
	public function __construct($workboardMode, $closedColumns = [])
	{
		$this->workboardMode = $workboardMode;
		$this->closedColumns = $closedColumns;
	}

	/**
	 * @param string $status
	 * @return string
	 */
	public function getCssClass($status)
	{
		if ($this->workboardMode && $status !== 'total')
		{
			return $this->openOrClosed($status) . ' ' . $this->statusToCssClass($status);
		} else
		{
			return $status;
		}
	}

	private function openOrClosed($status)
	{
		return in_array($status, $this->closedColumns) ? 'closed' : 'open';
	}

	private function statusToCssClass($status)
	{
		return preg_replace('/[^a-z0-9_-]+/', '-', strtolower($status));
	}
}
