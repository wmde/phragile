<?php
namespace Phragile;

use Phragile\Domain\Task;

class StatusByStatusFieldDispatcher implements StatusDispatcher {
	private $reviewTagPHID = null;

	public function __construct($reviewTagPHID)
	{
		$this->reviewTagPHID = $reviewTagPHID;
	}

	private function isTaskInReview(Task $task)
	{
		return !$this->isClosed($task) && in_array($this->reviewTagPHID, $task->getProjectPHIDs());
	}

	private function isTaskBeingDone(Task $task)
	{
		return !$this->isClosed($task) && !is_null($task->getAssigneePHID());
	}

	public function getStatus(Task $task)
	{
		if ($this->isTaskInReview($task))
		{
			return 'patch to review';
		} elseif ($this->isTaskBeingDone($task))
		{
			return 'doing';
		} else
		{
			return $task->getStatus();
		}
	}

	public function isClosed(Task $task)
	{
		return in_array($task->getStatus(), ['resolved', 'declined', 'invalid']);
	}
}
