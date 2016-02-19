<?php
namespace Phragile;

class PhabricatorAPI {
	private $client;
	private $priorities;

	public function __construct(\ConduitClient $client)
	{
		$this->client = $client;
		$this->priorities = \Config::get('phabricator.MANIPHEST_PRIORITIES', []);
	}

	public function setConduitAPIToken($token)
	{
		$this->client->setConduitToken($token);
	}

	public function createProject($title, array $members = [])
	{
		$response = $this->client->callMethodSynchronous(
			'project.create',
			[
				'name' => $title,
				'members' => $members
			]
		);

		return $response;
	}

	public function queryProjectByTitle($title)
	{
		return $this->queryProject(['names' => [$title]]);
	}

	public function queryProjectByID($id)
	{
		return $this->queryProject(['ids' => [$id]]);
	}

	private function queryProject($fields)
	{
		return head($this->client->callMethodSynchronous(
			'project.query',
			$fields
		)['data']);
	}

	/**
	 * Uses maniphest.search to search tasks for a project.
	 * @param string $projectPHID
	 * @param string $after - Cursor parameter to fetch the next page of tasks
	 * @return array
	 */
	public function searchTasksByProjectPHID($projectPHID, $after)
	{
		return $this->client->callMethodSynchronous(
			'maniphest.search',
			[
				'constraints' => ['projects' => [$projectPHID]],
				'attachments' => ['projects' => true],
				'after' => $after
			]
		);
	}

	public function createTask($projectPHID, array $task)
	{
		return $this->client->callMethodSynchronous(
			'maniphest.createtask',
			[
				'title' => $task['title'],
				'projectPHIDs' => [$projectPHID],
				'priority' => $this->priorities[$task['priority']],
				'auxiliary' => [
					env('MANIPHEST_STORY_POINTS_FIELD') => $task['points']
				]
			]
		);
	}

	public function updateTask($taskID, array $fields)
	{
		return $this->client->callMethodSynchronous(
			'maniphest.update',
			array_merge(
				['id' => $taskID],
				$fields
			)
		);
	}

	public function getTaskTransactions(array $ids)
	{
		return $this->client->callMethodSynchronous(
			'maniphest.gettasktransactions',
			[
				'ids' => $ids
			]
		);
	}

	public function getUserData(array $users)
	{
		return $this->client->callMethodSynchronous(
			'user.query',
			$users
		);
	}

	public function queryPHIDs(array $phids)
	{
		return $this->client->callMethodSynchronous(
			'phid.query',
			['phids' => $phids]
		);
	}

	public function getSprintDuration($phid)
	{
		try
		{
			$duration = $this->client->callMethodSynchronous(
				'sprint.getstartenddates',
				['project' => $phid]
			);

			return array_map(function($date)
			{
				return date('Y-m-d', $date);
			}, $duration);
		} catch (\ConduitClientException $e)
		{
			return null;
		}
	}
}
