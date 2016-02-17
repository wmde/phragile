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
		// TODO: This is not yet working around the pagination/search limit.
		return $this->phabricatorAPI->searchTasksByProjectPHID($projectPHID);
	}
}
