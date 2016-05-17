<?php
namespace Phragile;

use Phragile\Domain\Task;

interface StatusDispatcher {
	/**
	 * @param Task $task
	 * @return string $status
	 */
	public function getStatus(Task $task);

	/**
	 * @param Task $task
	 * @return boolean
	 */
	public function isClosed(Task $task);
}
