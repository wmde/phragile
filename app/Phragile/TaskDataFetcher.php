<?php
namespace Phragile;

class TaskDataFetcher {
	private $phabricatorAPI;

	public function __construct(PhabricatorAPI $phabricatorAPI)
	{
		$this->phabricatorAPI = $phabricatorAPI;
	}

	/**
	 * @param string $projectPHID
	 * @return array
	 */
	public function fetchProjectTasks($projectPHID)
	{
		$rawTaskData = [];
		$after = '0';
		while (!is_null($after))
		{
			$response = $this->phabricatorAPI->searchTasksByProjectPHID($projectPHID, $after);
			$after = $response['cursor']['after'];
			$rawTaskData = array_merge($rawTaskData, $response['data']);
		};

		return $rawTaskData;
	}
}
