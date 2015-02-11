<?php

namespace Phragile;

class AssigneeCollection {

	private $assignees;

	public function __construct(PhabricatorAPI $phabricator, array $tasks)
	{
		$assigneePHIDs = $this->extractUniqueAssignees($tasks);
		$this->assignees = $this->processAssignees(
			$phabricator->getUserData(['phids' => $assigneePHIDs])
		);
	}

	private function extractUniqueAssignees(array $tasks)
	{
		return array_unique(array_map(function($task)
		{
			return $task['ownerPHID'];
		}, $tasks));
	}

	private function processAssignees(array $assigneeData)
	{
		$assignees = [];

		foreach ($assigneeData as $assignee)
		{
			$assignees[$assignee['phid']] = $assignee;
		}

		return $assignees;
	}

	/**
	 * @param string $phid
	 * @return string
	 */
	public function getName($phid)
	{
		return isset($this->assignees[$phid]['userName']) ? $this->assignees[$phid]['userName'] : '-';
	}
}
