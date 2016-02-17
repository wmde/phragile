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
		return !$this->isClosed($task) && in_array($this->reviewTagPHID, $task['attachments']['projects']['projectPHIDs']);
	}

	private function isTaskBeingDone(array $task)
	{
		return !$this->isClosed($task) && !is_null($task['fields']['ownerPHID']);
	}

	public function getStatus(array $task)
	{
		if ($this->isTaskInReview($task)) return 'patch to review';
		elseif ($this->isTaskBeingDone($task)) return 'doing';
		else return $task['fields']['status']['value'];
	}

	public function isClosed(array $task)
	{
		return in_array($task['fields']['status']['value'], ['resolved', 'declined', 'invalid']);
	}
}
