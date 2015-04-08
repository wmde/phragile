<?php
namespace Phragile;

class ClosedTimeDispatcherFactory {
	private $workboardMode = false;

	public function __construct($workboardMode)
	{
		$this->workboardMode = $workboardMode;
	}

	public function createInstance()
	{
		return $this->workboardMode
			? new ClosedTimeByWorkboardDispatcher()
			: new ClosedTimeByStatusFieldDispatcher();
	}
}
