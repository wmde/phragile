<?php
use Phragile\Task;

class TaskTest extends TestCase {
	private $taskFields = [
		'title' => 'A task',
		'priority' => 'High',
		'points' => '13',
		'status' => 'Open',
		'closed' => true,
		'id' => '42',
		'assigneePHID' => 'PHID-1337',
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
		$this->setExpectedException(InvalidArgumentException::class);
		new Task($incompleteAttributes);
	}

	public function testHasTitle()
	{
		$task = new Task($this->taskFields);
		$this->assertSame($task->getTitle(), $this->taskFields['title']);
	}

	public function testHasPriority()
	{
		$task = new Task($this->taskFields);
		$this->assertSame($task->getPriority(), $this->taskFields['priority']);
	}

	public function testHasPoints()
	{
		$task = new Task($this->taskFields);
		$this->assertSame($task->getPoints(), $this->taskFields['points']);
	}

	public function testHasStatus()
	{
		$task = new Task($this->taskFields);
		$this->assertSame($task->getStatus(), $this->taskFields['status']);
	}

	public function testHasClosedField()
	{
		$task = new Task($this->taskFields);
		$this->assertSame($task->isClosed(), $this->taskFields['closed']);
	}

	public function testHasId()
	{
		$task = new Task($this->taskFields);
		$this->assertSame($task->getId(), $this->taskFields['id']);
	}

	public function testHasAssigneePHID()
	{
		$task = new Task($this->taskFields);
		$this->assertSame($task->getAssigneePHID(), $this->taskFields['assigneePHID']);
	}

	public function testCanSetAndGetAssignee()
	{
		$task = new Task($this->taskFields);
		$assignee = 'Meh';
		$task->setAssigneeName($assignee);
		$this->assertSame($assignee, $task->getAssigneeName());
	}

	public function testCanSetAndGetCssClass()
	{
		$task = new Task($this->taskFields);
		$class = 'foo';
		$task->setCssClass($class);
		$this->assertSame($class, $task->getCssClass());
	}
}
