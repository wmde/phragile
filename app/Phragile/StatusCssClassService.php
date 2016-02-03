<?php
namespace Phragile;

class StatusCssClassService {
	private $worboardMode = false;
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
			return in_array($status, $this->closedColumns) ? 'closed' : 'open';
		else return $status;
	}
}
