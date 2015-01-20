<?php
namespace Phragile;

class PhabricatorAPI {
	public function __construct(\ConduitClient $client)
	{
		$this->client = $client;
	}

	public function connect($user, $certificate)
	{
		return $this->client->callMethodSynchronous(
			'conduit.connect',
			[
				'client' => 'Phragile',
				'clientVersion' => 1,
				'user' => $user,
				'certificate' => $certificate
			]
		);
	}

	public function authenticate($accessToken)
	{
		$response = $this->client->callMethodSynchronous(
			"user.whoami?access_token=$accessToken",
			[]
		);

		return isset($response['phid']) ? $response : null;
	}

	public function createProject($title)
	{
		$response = $this->client->callMethodSynchronous(
			'project.create',
			[
				'name' => $title,
				'members' => []
			]
		);

		return $response;
	}

	public function queryProjectByTitle($title)
	{
		return head($this->client->callMethodSynchronous(
			'project.query',
			[
				'names' => [$title]
			]
		)['data']);
	}

	public function queryTasksByProject($projectPHID)
	{
		return $this->client->callMethodSynchronous(
			'maniphest.query',
			[
				'projectPHIDs' => [$projectPHID]
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
				'priority' => $_ENV['MANIPHEST_PRIORITY_MAPPING.' . $task['priority']],
				'auxiliary' => [
					$_ENV['MANIPHEST_STORY_POINTS_FIELD'] => $task['points']
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
}
