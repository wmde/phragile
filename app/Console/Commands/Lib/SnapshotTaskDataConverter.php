<?php

namespace App\Console\Commands\Lib;

use Phragile\Domain\Task;
use Phragile\TaskRawDataProcessor;

class SnapshotTaskDataConverter {

	public function convert(array $taskData)
	{
		if ($this->taskDataIsInManiphestQueryFormat($taskData))
		{
			return array_values(array_map([$this, 'convertTaskInManiphestQueryFormat'], $taskData));
		}
		if (!$this->taskDataIsInManiphestSearchFormat($taskData))
		{
			return $taskData;
		}
		$processor = new TaskRawDataProcessor();
		return array_map(
			function(Task $task)
			{
				return $task->getData();
			},
			$processor->process($taskData)
		);
	}

	public function needsConversion(array $taskData)
	{
		return $this->taskDataIsInManiphestSearchFormat($taskData) || $this->taskDataIsInManiphestQueryFormat($taskData);
	}

	private function taskDataIsInManiphestSearchFormat(array $taskData)
	{
		return array_keys($taskData) === range(0, count($taskData) - 1) && is_array($taskData[0]) &&
		array_key_exists('fields', $taskData[0]) && array_key_exists('attachments', $taskData[0]);
	}

	private function taskDataIsInManiphestQueryFormat(array $taskData)
	{
		// task array in maniphest.query response is PHID-indexed
		return array_keys($taskData) !== range(0, count($taskData) - 1);
	}

	private function convertTaskInManiphestQueryFormat(array $task)
	{
		$points = isset(
			$task['auxiliary'][env('MANIPHEST_STORY_POINTS_FIELD')]
		) ? $task['auxiliary'][env('MANIPHEST_STORY_POINTS_FIELD')] : 0;

		return [
			'id' => (int)$task['id'],
			'title' => $task['title'],
			'priority' => $task['priority'],
			'status' => $task['status'],
			'points' => $points,
			'projectPHIDs' => $task['projectPHIDs'],
			'assigneePHID' => $task['ownerPHID'],
			'customFields' => $this->convertManiphestQueryCustomFields($task)
		];
	}

	private function convertManiphestQueryCustomFields(array $task)
	{
		$fields = [];

		foreach ($task['auxiliary'] as $name => $value)
		{
			$fields[$this->extractFieldName($name)] = $value;
		}

		return $fields;
	}

	/**
	 * Format of old custom field was e.g. std:maniphest:WMDE:story_points or isdc:sprint:storypoints.
	 * Equivalent new format would be custom.WMDE_story_points or custom.storypoints.
	 * @param string $s
	 * @return string
	 */
	private function extractFieldName($s)
	{
		if (count(explode(':', $s)) < 3)
		{
			return $s;
		}
		return implode(':',
			array_slice(explode(':', $s), 2)
		);
	}
}
