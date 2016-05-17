<?php

namespace Phragile;

use Phragile\Domain\Task as DomainTask;
use Phragile\Presentation\Task as PresentationTask;

class TaskPresenter {

	private $statusDispatcher;
	private $options;

	public function __construct(StatusDispatcher $statusDispatcher, array $options)
	{
		$this->statusDispatcher = $statusDispatcher;
		$this->options = $options;
	}

	/**
	 * @param Domain\Task[] $tasks
	 * @return Presentation\Task[]
	 */
	public function render(array $domainTasks)
	{
		return array_filter(
			array_map(function(DomainTask $domainTask)
			{
				return new PresentationTask(
					$domainTask,
					$this->statusDispatcher->getStatus($domainTask),
					$this->statusDispatcher->isClosed($domainTask),
					$this->options['ignore_estimates'] ? 1 : $this->getPoints($domainTask)
				);
			}, array_values($domainTasks)),
			function(PresentationTask $presentationTask)
			{
				return !in_array($presentationTask->getStatus(), $this->options['ignored_columns']);
			}
		);
	}

	private function getPoints(DomainTask $task)
	{
		$customFields = $task->getCustomFields();
		if (env('MANIPHEST_STORY_POINTS_FIELD') && isset($customFields[env('MANIPHEST_STORY_POINTS_FIELD')]))
		{
			return $customFields[env('MANIPHEST_STORY_POINTS_FIELD')];
		} else
		{
			return $task->getPoints();
		}
	}

}
