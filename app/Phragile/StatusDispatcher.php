<?php
namespace Phragile;

interface StatusDispatcher {
	/**
	 * @param array $task
	 * @return string $status
	 */
	public function getStatus(array $task);
}
