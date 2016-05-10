<?php

namespace Phragile\Tests;

use Phragile\Domain\Task;
use Phragile\TaskRawDataProcessor;

/**
 * @covers Phragile\TaskRawDataProcessor
 */
class TaskRawDataProcessorTest extends \PHPUnit_Framework_TestCase {

	public function testProcessCreatesTasks()
	{
		$taskRawData = [
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
					'points' => 8,
					'spacePHID' => null,
					'dateCreated' => 1455539048,
					'dateModified' => 1455539922,
					'policy' => [
						'view' => 'users',
						'edit' => 'users'
					],
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
						'value' => 'resolved',
						'name' => 'Resolved',
						'color' => null
					],
					'priority' => [
						'value' => 90,
						'subpriority' => 0,
						'name' => 'High',
						'color' => 'violet'
					],
					'points' => 24,
					'spacePHID' => null,
					'dateCreated' => 1455539073,
					'dateModified' => 1455621580,
					'policy' => [
						'view' => 'users',
						'edit' => 'users'
					],
				],
				'attachments' => ['projects' => ['projectPHIDs' => ['PHID-PROJ-0123456789abcdef0123']]]
			],
		];

		$processor = new TaskRawDataProcessor();
		$this->assertEquals(
			[
				new Task([
					'id' => 321,
					'title' => 'task1',
					'priority' => 'Needs Triage',
					'status' => 'open',
					'points' => 8,
					'projectPHIDs' => [],
					'assigneePHID' => null,
				]),
				new Task([
					'id' => 322,
					'title' => 'task2',
					'priority' => 'High',
					'status' => 'resolved',
					'points' => 24,
					'projectPHIDs' => ['PHID-PROJ-0123456789abcdef0123'],
					'assigneePHID' => 'PHID-USER-orako562kbdgbdwvteo7',
				]),
			],
			$processor->process($taskRawData)
		);
	}

	public function testProcessConsidersCustomFields()
	{
		$taskRawData = [
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
			],
		];

		$processor = new TaskRawDataProcessor();
		$this->assertEquals(
			[
				new Task([
					'id' => 321,
					'title' => 'task1',
					'priority' => 'Needs Triage',
					'status' => 'open',
					'points' => null,
					'projectPHIDs' => [],
					'assigneePHID' => null,
					'customFields' => ['WMDE:story_points' => 8],
				]),

			],
			$processor->process($taskRawData)
		);
	}

}
