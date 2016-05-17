<?php

namespace Phragile\Tests;

use App\Console\Commands\Lib\SnapshotDataConverter;

class SnapshotDataConverterTest extends TestCase {
	public function testGivenManiphestQueryData_ReturnsManiphestSearchData()
	{
		$customField = 'WMDE:storypoints';
		$this->queryTaskData['0']['auxiliary']['std:maniphest:' . $customField] = 'foo';
		$this->queryTaskData['0']['auxiliary'][env('MANIPHEST_STORY_POINTS_FIELD')] = '5';
		$convertedData = (new SnapshotDataConverter($this->queryTaskData))->convert();

		$this->assertSame($this->searchTaskData[0]['id'], $convertedData[0]['id']);
		$this->assertSame($this->searchTaskData[0]['phid'], $convertedData[0]['phid']);
		$this->assertSame($this->searchTaskData[0]['fields']['name'], $convertedData[0]['fields']['name']);
		$this->assertSame($this->searchTaskData[0]['fields']['authorPHID'], $convertedData[0]['fields']['authorPHID']);
		$this->assertSame($this->searchTaskData[0]['fields']['ownerPHID'], $convertedData[0]['fields']['ownerPHID']);
		$this->assertSame($this->searchTaskData[0]['fields']['status']['value'], $convertedData[0]['fields']['status']['value']);
		$this->assertSame($this->searchTaskData[0]['fields']['priority']['name'], $convertedData[0]['fields']['priority']['name']);
		$this->assertSame($this->searchTaskData[0]['fields']['points'], $convertedData[0]['fields']['points']);
		$this->assertSame('foo', $convertedData[0]['fields']['custom.' . $customField]);
		$this->assertSame($this->queryTaskData['0']['projectPHIDs'], $convertedData[0]['attachments']['projects']['projectPHIDs']);
	}

	private $queryTaskData = [
		'0' => [
			'id' => '127180',
			'phid' => 'PHID-TASK-4kvxc4re6xrshgxtfajl',
			'authorPHID' => 'PHID-USER-t4sxxglz6yyrgxeib43i',
			'ownerPHID' => 'PHID-USER-5dv7dcltvyvolwzbm2af',
			'ccPHIDs' => [
				'PHID-USER-5dv7dcltvyvolwzbm2af',
				'PHID-USER-fn7qnpccfbitivgtw2rt',
				'PHID-USER-lltif2drabccdkwhet7x'
			],
			'status' => 'open',
			'statusName' => 'Open',
			'isClosed' => false,
			'priority' => 'High',
			'priorityColor' => 'red',
			'title' => '[Phragile] Migration script for old snapshots',
			'description' => 'Snapshot data needs to be migrated to a new format since we are going to
				abandon maniphest.query in favor of maniphest.search.',
			'projectPHIDs' => [
				'PHID-PROJ-ptnfbfyq36kkebaxugcz',
				'PHID-PROJ-tazsyaydzpbd643tderv',
				'PHID-PROJ-knyj2bgnrkrwu72n27bg'
			],
			'uri' => 'https://phabricator.wikimedia.org/T127180',
			'auxiliary' => [
				'std:maniphest:security_topic' => 'default',
			],
			'objectName' => 'T127180',
			'dateCreated' => '1455716487',
			'dateModified' => '1455880296',
			'dependsOnTaskPHIDs' => [
				'PHID-TASK-tuaxg2zcafwmpoe2d5ys'
			]
		]
	];

	private $searchTaskData = [
		[
			'id' => 127180,
			'type' => 'TASK',
			'phid' => 'PHID-TASK-4kvxc4re6xrshgxtfajl',
			'fields' => [
				'name' => '[Phragile] Migration script for old snapshots',
				'authorPHID' => 'PHID-USER-t4sxxglz6yyrgxeib43i',
				'ownerPHID' => 'PHID-USER-5dv7dcltvyvolwzbm2af',
				'status' => [
					'value' => 'open',
					'name' => 'Open',
					'color' => null
				],
				'priority' => [
					'value' => 80,
					'subpriority' => -8.5312317223004e-5,
					'name' => 'High',
					'color' => 'red'
				],
				'points' => '5',
				'spacePHID' => 'PHID-SPCE-6l6g5p53yi3mypnlpxjw',
				'dateCreated' => 1455716487,
				'dateModified' => 1455880296,
				'policy' => [
					'view' => 'public',
					'edit' => 'users'
				],
				'custom.security_topic' => 'default',
				'custom.external_reference' => null
			],
			'attachments' => [
				'projects' => [
					'projectPHIDs' => [
						'PHID-PROJ-ptnfbfyq36kkebaxugcz',
						'PHID-PROJ-tazsyaydzpbd643tderv',
						'PHID-PROJ-knyj2bgnrkrwu72n27bg'
					]
				]
			]
		]
	];
}
