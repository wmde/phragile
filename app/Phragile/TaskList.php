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
				'status' => $task['status'],
				'story_points' => $task['auxiliary'][$_ENV['MANIPHEST_STORY_POINTS_FIELD']],
			];
		}, array_values($this->phabricator->queryTasksByProject($phid)));
	}

	public function getTasks()
	{
		return $this->tasks;
	}
}
