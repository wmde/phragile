<?php

namespace Phragile\ActionHandler;

use Phragile\PhabricatorAPI;

class SprintNotFoundActionHandler {
	private $phabricatorAPI;

	public function __construct(PhabricatorAPI $phabricatorAPI)
	{
		$this->phabricatorAPI = $phabricatorAPI;
	}

	public function performAction($sprintPhabricatorId, $isLoggedIn)
	{
		$phabricatorProject = $this->phabricatorAPI->queryProjectByID($sprintPhabricatorId);
		if (!$phabricatorProject)
		{
			return \View::make('sprint.not_found');
		}
		if (!$isLoggedIn)
		{
			return \View::make('sprint.connect_unauthorized', ['phabricatorProject' => $phabricatorProject]);
		}

		$duration = $this->phabricatorAPI->getSprintDuration($phabricatorProject['phid']);

		return \View::make('sprint.connect', [
			'duration' => $duration,
			'phabricatorProject' => $phabricatorProject,
			'projects' => \Project::orderBy('title')->lists('title', 'id'),
		]);
	}

}
