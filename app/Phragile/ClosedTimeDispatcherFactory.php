<?php
namespace Phragile;

class ClosedTimeDispatcherFactory {
	private $workboardMode = false;

	/**
	 * @param bool $workboardMode
	 */
	public function __construct($workboardMode)
	{
		$this->workboardMode = $workboardMode;
	}

	/**
	 * @return ClosedTimeDispatcher
	 */
	public function createInstance()
	{
		return $this->workboardMode
			? new ClosedTimeByWorkboardDispatcher()
			: new ClosedTimeByStatusFieldDispatcher();
	}
}
