<?php

namespace Phragile\Tests;

use App\Console\Commands\Lib\SnapshotTaskDataConverter;

class SnapshotTaskDataConverterTest extends TestCase {

	public function testGivenManiphestQueryData_needsConversionReturnsTrue()
	{
		$converter = new SnapshotTaskDataConverter();
		$this->assertTrue($converter->needsConversion($this->getQueryTaskData()));
	}

	public function testGivenManiphestSearchData_needsConversionReturnsTrue()
	{
		$converter = new SnapshotTaskDataConverter();
		$this->assertTrue($converter->needsConversion($this->searchTaskData));
	}

	public function testGivenConvertedData_needsConversionReturnsFalse()
	{
		$converter = new SnapshotTaskDataConverter();
		$this->assertFalse($converter->needsConversion($this->getConvertedData()));
	}

	public function testGivenDataInUnsupportedFormat_needsConversionReturnsFalse()
	{
		$converter = new SnapshotTaskDataConverter();
		$this->assertFalse($converter->needsConversion([['foo' => 'bar']]));
	}

	public function testGivenManiphestQueryData_convertReturnsConvertedData()
	{
		$expectedConvertedData = $this->getConvertedData();
		// TODO: this is a bit silly but as long as tests use the production .env file there is
		// no value of MANIPHEST_STORY_POINTS_FIELD test could rely on. This could be possibly
		// solved by introducing test .env file with some particular value MANIPHEST_STORY_POINTS_FIELD
		$customStoryPointsField = $this->strAfterSecondColon(env('MANIPHEST_STORY_POINTS_FIELD'));
		$expectedConvertedData[0]['customFields']['WMDE:storypoints'] = 'foo';
		$expectedConvertedData[0]['customFields'][$customStoryPointsField] = '5';

		$queryTaskData = $this->getQueryTaskData();
		$customField = 'WMDE:storypoints';
		$queryTaskData['PHID-TASK-4kvxc4re6xrshgxtfajl']['auxiliary']['std:maniphest:' . $customField] = 'foo';
		$queryTaskData['PHID-TASK-4kvxc4re6xrshgxtfajl']['auxiliary'][env('MANIPHEST_STORY_POINTS_FIELD')] = '5';
		$convertedTaskData = (new SnapshotTaskDataConverter())->convert($queryTaskData);

		$this->assertSame($expectedConvertedData, $convertedTaskData);
	}

	public function testGivenManiphestSearchData_convertReturnsConvertedData()
	{
		$expectedConvertedData = $this->getConvertedData();
		$expectedConvertedData[0]['customFields']['external_reference'] = null;
		$convertedTaskData = (new SnapshotTaskDataConverter())->convert($this->searchTaskData);

		$this->assertSame($expectedConvertedData, $convertedTaskData);
	}

	public function testGivenConvertedData_convertReturnsUnchangedData()
	{
		$expectedConvertedData = $this->getConvertedData();
		$convertedTaskData = (new SnapshotTaskDataConverter())->convert($this->getConvertedData());
		$this->assertSame($expectedConvertedData, $convertedTaskData);
	}

	private function getQueryTaskData()
	{
		return [
			'PHID-TASK-4kvxc4re6xrshgxtfajl' => [
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
	}

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

	private function getConvertedData()
	{
		return [
			[
				'id' => 127180,
				'title' => '[Phragile] Migration script for old snapshots',
				'priority' => 'High',
				'status' => 'open',
				'points' => '5',
				'projectPHIDs' => [
					'PHID-PROJ-ptnfbfyq36kkebaxugcz',
					'PHID-PROJ-tazsyaydzpbd643tderv',
					'PHID-PROJ-knyj2bgnrkrwu72n27bg'
				],
				'assigneePHID' => 'PHID-USER-5dv7dcltvyvolwzbm2af',
				'customFields' => ['security_topic' => 'default'],
			]
		];
	}

	private function strAfterSecondColon($s)
	{
		return implode(':',
			array_slice(explode(':', $s), 2)
		);
	}

}
