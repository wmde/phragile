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
		return $this->workboardMode
			? new StatusByWorkboardDispatcher()
			: new StatusByStatusFieldDispatcher();
	}
}
