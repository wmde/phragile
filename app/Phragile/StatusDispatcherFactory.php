<?php
namespace Phragile;

class StatusDispatcherFactory {
	private $workboardMode = false;

	public function __construct($workboardMode)
	{
		$this->workboardMode = $workboardMode;
	}

	/**
	 * @return StatusDispatcher
	 */
	public function createInstance()
	{
		if ($this->workboardMode) return new StatusByWorkboardDispatcher();
		else return new StatusByStatusFieldDispatcher();
	}
}
