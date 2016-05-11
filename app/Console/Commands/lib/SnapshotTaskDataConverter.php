<?php

namespace App\Console\Commands\Lib;

class SnapshotTaskDataConverter {

	public function convert(array $taskData)
	{
		return array_values(array_map(array($this, 'convertToManiphestSearchResponse'), $taskData));
	}

	private function convertToManiphestSearchResponse(array $task)
	{
		$points = isset(
			$task['auxiliary'][env('MANIPHEST_STORY_POINTS_FIELD')]
		) ? $task['auxiliary'][env('MANIPHEST_STORY_POINTS_FIELD')] : 0;
		return [
			'id' => (int)$task['id'],
			'type' => 'TASK',
			'phid' => $task['phid'],
			'fields' => array_merge(
				$this->convertCustomFields($task), [
				'name' => $task['title'],
				'authorPHID' => $task['authorPHID'],
				'ownerPHID' => $task['ownerPHID'],
				'status' => [
					'value' => $task['status'],
					'name' => null,
					'color' => null
				],
				'priority' => [
					'value' => null,
					'subpriority' => null,
					'name' => $task['priority'],
				],
				'points' => $points,
			]),
			'attachments' => ['projects' => ['projectPHIDs' => $task['projectPHIDs']]]
		];
	}

	private function convertCustomFields(array $task)
	{
		$fields = [];

		foreach ($task['auxiliary'] as $name => $value)
		{
			$fields['custom.' . $this->strAfterSecondColon($name)] = $value;
		}

		return $fields;
	}

	/**
	 * Format of old custom field was e.g. std:maniphest:WMDE:story_points or isdc:sprint:storypoints.
	 * Equivalent new format would be custom.WMDE_story_points or custom.storypoints.
	 * @param string $s
	 * @return string
	 */
	private function strAfterSecondColon($s)
	{
		return implode(':',
			array_slice(explode(':', $s), 2)
		);
	}
}
