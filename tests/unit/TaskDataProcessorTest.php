<?php

use Phragile\StatusByStatusFieldDispatcher;
use Phragile\TaskDataProcessor;

class TaskDataProcessorTest extends TestCase
{
	public function testCreatesTasksCorrectly()
	{
		$statusDispatcher = new StatusByStatusFieldDispatcher('');
		$tasks = (new TaskDataProcessor(
			$statusDispatcher,
			['ignored_columns' => [], 'ignore_estimates' => false]
		))->process($this->taskRawData);

		$taskRawDataNum = count($this->taskRawData);
		for ($i = 0; $i < $taskRawDataNum; $i++)
		{
			$rawTask = $this->taskRawData[$i];
			$task = $tasks[$i];

			$this->assertSame($rawTask['fields']['name'], $task->getTitle());
			$this->assertSame($rawTask['fields']['priority']['name'], $task->getPriority());
			$this->assertSame($rawTask['fields']['points'], $task->getPoints());
			$this->assertSame($statusDispatcher->getStatus($rawTask), $task->getStatus());
			$this->assertSame($rawTask['id'], $task->getId());
			$this->assertSame($rawTask['fields']['ownerPHID'], $task->getAssigneePHID());
		}
	}

	/**
	 * This test is using a StatusByStatusFieldDispatcher since it is easier to set up even though the tested
	 * functionality is supposed to be used for ignoring columns.
	 */
	public function testIgnoreToDoStatus()
	{
		$rawData = [
			[
				'id' => 1,
				'fields' => [
					'name' => 'To Do Task',
					'priority' => ['name' => 'High'],
					'status' => ['value' => 'To Do'],
					'ownerPHID' => null,
					'points' => 2,
				],
				'attachments' => ['projects' => ['projectPHIDs' => []]]
			],
			[
				'id' => 2,
				'fields' => [
					'name' => 'Resolved Task',
					'priority' => ['name' => 'High'],
					'status' => ['value' => 'resolved'],
					'ownerPHID' => null,
					'points' => 3,
				],
				'attachments' => ['projects' => ['projectPHIDs' => []]]
			]
		];

		$tasks = (new TaskDataProcessor(
			new StatusByStatusFieldDispatcher(''),
			['ignored_columns' => ['To Do'], 'ignore_estimates' => false]
		))->process($rawData);
		$this->assertCount(1, $tasks);
		$this->assertSame('resolved', array_values($tasks)[0]->getStatus());
	}

	public function testGivenIgnoreEstimatesTrue_GiveEachTask1Point()
	{
		$statusDispatcher = new StatusByStatusFieldDispatcher('');
		$tasks = (new TaskDataProcessor(
			$statusDispatcher,
			['ignored_columns' => [], 'ignore_estimates' => true]
		))->process($this->taskRawData);

		$taskRawDataNum = count($this->taskRawData);
		for ($i = 0; $i < $taskRawDataNum; $i++)
		{
			$this->assertSame(1, $tasks[$i]->getPoints());
		}
	}

	private $taskRawData = [ // Results of a maniphest.search call
		[
			'id' => 325,
			'type' => 'TASK',
			'phid' => 'PHID-TASK-rl7r3g3kc3y2tbxlwsc2',
			'fields' => [
				'name' => 'task5',
				'authorPHID' => 'PHID-USER-orako562kbdgbdwvteo7',
				'ownerPHID' => null,
				'status' => [
					'value' => 'open',
					'name' => 'Open',
					'color' => null
				],
				'priority' => [
					'value' => 90,
					'subpriority' => 0,
					'name' => 'Needs Triage',
					'color' => 'violet'
				],
				'points' => '13',
				'spacePHID' => null,
				'dateCreated' => 1455539678,
				'dateModified' => 1455548877,
				'policy' => [
					'view' => 'users',
					'edit' => 'users'
				],
				'custom.WMDE:story_points' => 2,
			],
			'attachments' => ['projects' => ['projectPHIDs' => []]]
		],
		[
			'id' => 324,
			'type' => 'TASK',
			'phid' => 'PHID-TASK-oiz3ted6g3yyux7homi6',
			'fields' => [
				'name' => 'task4',
				'authorPHID' => 'PHID-USER-orako562kbdgbdwvteo7',
				'ownerPHID' => null,
				'status' => [
					'value' => 'open',
					'name' => 'Open',
					'color' => null
				],
				'priority' => [
					'value' => 90,
					'subpriority' => 0,
					'name' => 'Needs Triage',
					'color' => 'violet'
				],
				'points' => null,
				'spacePHID' => null,
				'dateCreated' => 1455539134,
				'dateModified' => 1455539981,
				'policy' => [
					'view' => 'users',
					'edit' => 'users'
				],
				'custom.WMDE:story_points' => 3,
			],
			'attachments' => ['projects' => ['projectPHIDs' => []]]
		],
		[
			'id' => 323,
			'type' => 'TASK',
			'phid' => 'PHID-TASK-dywm65et6m5s627svkap',
			'fields' => [
				'name' => 'task3',
				'authorPHID' => 'PHID-USER-orako562kbdgbdwvteo7',
				'ownerPHID' => null,
				'status' => [
					'value' => 'open',
					'name' => 'Open',
					'color' => null
				],
				'priority' => [
					'value' => 90,
					'subpriority' => 0,
					'name' => 'Needs Triage',
					'color' => 'violet'
				],
				'points' => null,
				'spacePHID' => null,
				'dateCreated' => 1455539110,
				'dateModified' => 1455539966,
				'policy' => [
					'view' => 'users',
					'edit' => 'users'
				],
				'custom.WMDE:story_points' => 2,
			],
			'attachments' => ['projects' => ['projectPHIDs' => []]]
		],
		[
			'id' => 322,
			'type' => 'TASK',
			'phid' => 'PHID-TASK-7i5ytqz2cwrwysquktc5',
			'fields' => [
				'name' => 'task2',
				'authorPHID' => 'PHID-USER-orako562kbdgbdwvteo7',
				'ownerPHID' => 'PHID-USER-orako562kbdgbdwvteo7',
				'status' => [
					'value' => 'open',
					'name' => 'Open',
					'color' => null
				],
				'priority' => [
					'value' => 90,
					'subpriority' => 0,
					'name' => 'Needs Triage',
					'color' => 'violet'
				],
				'points' => null,
				'spacePHID' => null,
				'dateCreated' => 1455539073,
				'dateModified' => 1455621580,
				'policy' => [
					'view' => 'users',
					'edit' => 'users'
				],
				'custom.WMDE:story_points' => 24,
			],
			'attachments' => ['projects' => ['projectPHIDs' => []]]
		],
		[
			'id' => 321,
			'type' => 'TASK',
			'phid' => 'PHID-TASK-e5t6hiqw6nqbrdr5agfk',
			'fields' => [
				'name' => 'task1',
				'authorPHID' => 'PHID-USER-orako562kbdgbdwvteo7',
				'ownerPHID' => null,
				'status' => [
					'value' => 'open',
					'name' => 'Open',
					'color' => null
				],
				'priority' => [
					'value' => 90,
					'subpriority' => 0,
					'name' => 'Needs Triage',
					'color' => 'violet'
				],
				'points' => null,
				'spacePHID' => null,
				'dateCreated' => 1455539048,
				'dateModified' => 1455539922,
				'policy' => [
					'view' => 'users',
					'edit' => 'users'
				],
				'custom.WMDE:story_points' => 8,
			],
			'attachments' => ['projects' => ['projectPHIDs' => []]]
		]
	];
}
