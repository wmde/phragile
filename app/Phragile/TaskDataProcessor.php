<?php
namespace Phragile;

class TaskDataProcessor {
	private $statusDispatcher;
	private $options;

	public function __construct(StatusDispatcher $statusDispatcher, array $options)
	{
		$this->statusDispatcher = $statusDispatcher;
		$this->options = $options;
	}

	/**
	 * @param $rawData
	 * @return Task[]
	 */
	public function process($rawData)
	{
		return array_filter(
			array_map(function($task)
			{
				return new Task([
					'title' => $task['fields']['name'],
					'priority' => $task['fields']['priority']['name'],
					'status' => $this->statusDispatcher->getStatus($task),
					'points' => $this->options['ignore_estimates'] ? 1 : $this->getPoints($task),
					'closed' => $this->statusDispatcher->isClosed($task),
					'id' => $task['id'],
					'assigneePHID' => $task['fields']['ownerPHID'],
				]);
			}, array_values($rawData)),
			function(Task $task)
			{
				return !in_array($task->getStatus(), $this->options['ignored_columns']);
			}
		);
	}

	private function getPoints(array $task)
	{
		if (env('MANIPHEST_STORY_POINTS_FIELD') && isset($task['fields']['custom.' . env('MANIPHEST_STORY_POINTS_FIELD')]))
		{
			return $task['fields']['custom.' . env('MANIPHEST_STORY_POINTS_FIELD')];
		} else
		{
			return $task['fields']['points'];
		}
	}
}
