<?php

namespace Phragile\Tests\Domain;

use Phragile\Domain\Task;

/**
 * @covers Phragile\Domain\Task
 */
class TaskTest extends \PHPUnit_Framework_TestCase {

	private $taskFields = [
		'title' => 'A task',
		'priority' => 'High',
		'points' => 13,
		'status' => 'open',
		'id' => 42,
		'assigneePHID' => 'PHID-USER-1337',
		'projectPHIDs' => ['PHID-PROJ-001'],
	];

	public function incompleteAttributes()
	{
		$attributeCombinations = [];

		foreach (array_keys($this->taskFields) as $key)
		{
			$combination = $this->taskFields;
			unset($combination[$key]);
			$attributeCombinations[] = [$combination];
		}

		return $attributeCombinations;
	}

	/**
	 * @dataProvider incompleteAttributes
	 */
	public function testThrowsExceptionWithMissingAttributes($incompleteAttributes)
	{
		$this->setExpectedException(\InvalidArgumentException::class);
		new Task($incompleteAttributes);
	}

	public function testConstructorSetsFields()
	{
		$task = new Task($this->taskFields);
		$this->assertEquals(42, $task->getId());
		$this->assertEquals('A task', $task->getTitle());
		$this->assertEquals('open', $task->getStatus());
		$this->assertEquals('High', $task->getPriority());
		$this->assertEquals(13, $task->getPoints());
		$this->assertEquals(['PHID-PROJ-001'], $task->getProjectPHIDs());
		$this->assertEquals('PHID-USER-1337', $task->getAssigneePHID());
		$this->assertEmpty($task->getCustomFields());
	}

	public function testConstructorSetsCustomFields()
	{
		$fields = $this->taskFields;
		$fields['customFields'] = [
			'foo' => 'bar',
			'baz' => 123,
		];
		$task = new Task($fields);
		$this->assertEquals(['foo' => 'bar', 'baz' => 123], $task->getCustomFields());
	}

	public function testThrowsExceptionWhenCustomFieldsNotAnArray()
	{
		$fields = $this->taskFields;
		$fields['customFields'] = 'foo';
		$this->setExpectedException(\InvalidArgumentException::class);
		new Task($fields);
	}

	public function testGetData()
	{
		$task = new Task($this->taskFields);
		$this->assertEquals(
			[
				'id' => 42,
				'title' => 'A task',
				'priority' => 'High',
				'status' => 'open',
				'points' => 13,
				'projectPHIDs' => ['PHID-PROJ-001'],
				'assigneePHID' => 'PHID-USER-1337',
				'customFields' => [],
			],
			$task->getData()
		);
	}

}
