<?php

namespace Phragile;

use Phragile\Domain\Task;

class TaskRawDataProcessor {

	/**
	 * @param array $rawData
	 * @return Task[]
	 */
	public function process(array $rawData)
	{
		return array_map(function($rawTask)
		{
			return new Task([
				'title' => $rawTask['fields']['name'],
				'priority' => $rawTask['fields']['priority']['name'],
				'status' => $rawTask['fields']['status']['value'],
				'points' => $rawTask['fields']['points'],
				'id' => $rawTask['id'],
				'projectPHIDs' => $rawTask['attachments']['projects']['projectPHIDs'],
				'assigneePHID' => $rawTask['fields']['ownerPHID'],
				'customFields' => $this->getCustomFields($rawTask),
			]);
		}, array_values($rawData));
	}

	private function getCustomFields(array $rawTaskData)
	{
		$customFields = [];
		// After dropping support for PHP 5.5 array_filter with ARRAY_FILTER_USE_KEY could be used instead of a loop!
		foreach ($rawTaskData['fields'] as $key => $value)
		{
			if (substr($key, 0, 7) === 'custom.')
			{
				$customFields[substr($key, 7)] = $value;
			}
		}
		return $customFields;
	}

}
