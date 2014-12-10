<?php

namespace Phragile;

class TaskList {
	private $tasks = null;
	private $phabricator = null;

	public function __construct(PhabricatorAPI $phabricator, $phid)
	{
		$this->phabricator = $phabricator;
		$this->tasks = $this->fetchTasks($phid);
	}

	private function fetchTasks($phid)
	{
		return array_map(function($task)
		{
			return [
				'title' => $task['title'],
				'priority' => $task['priority'],
				'status' => $this->taskStatus($task),
				'story_points' => $task['auxiliary'][$_ENV['MANIPHEST_STORY_POINTS_FIELD']],
			];
		}, array_values($this->phabricator->queryTasksByProject($phid)));
	}

	public function getTasks()
	{
		return $this->tasks;
	}

	private function taskStatus(array $task)
	{
		return !$task['isClosed'] && in_array($_ENV['REVIEW_TAG_PHID'], $task['projectPHIDs'])
			? 'patch to review'
			: $task['status'];
	}
}
