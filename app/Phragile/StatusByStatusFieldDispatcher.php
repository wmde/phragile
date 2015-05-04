<?php
namespace Phragile;

class StatusByStatusFieldDispatcher implements StatusDispatcher {
	private $reviewTagPHID = null;

	public function __construct($reviewTagPHID)
	{
		$this->reviewTagPHID = $reviewTagPHID;
	}

	private function isTaskInReview(array $task)
	{
		return !$task['isClosed'] && in_array($this->reviewTagPHID, $task['projectPHIDs']);
	}

	private function isTaskBeingDone(array $task)
	{
		return !$task['isClosed'] && !is_null($task['ownerPHID']);
	}

	public function getStatus(array $task)
	{
		if ($this->isTaskInReview($task)) return 'patch to review';
		elseif ($this->isTaskBeingDone($task)) return 'doing';
		else return $task['status'];
	}

	public function isClosed(array $task)
	{
		return $task['isClosed'];
	}
}
