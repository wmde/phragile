<?php

namespace Phragile\Tests\Presentation;

use Phragile\Domain\Task as DomainTask;
use Phragile\Presentation\Task;
use Phragile\Tests\TestCase;

/**
 * @covers Phragile\Presentation\Task
 */
class TaskTest extends TestCase {

	private function newDomainTask()
	{
		return new DomainTask([
			'title' => 'A task',
			'priority' => 'High',
			'points' => 13,
			'status' => 'open',
			'id' => 42,
			'assigneePHID' => 'PHID-USER-1337',
			'projectPHIDs' => ['PHID-PROJ-123'],
		]);
	}

	public function testConstructorSetsFields()
	{
		$task = new Task($this->newDomainTask(), 'doing', Task::OPEN_TASK, 1);
		$this->assertSame(42, $task->getId());
		$this->assertSame('A task', $task->getTitle());
		$this->assertSame('High', $task->getPriority());
		$this->assertSame('doing', $task->getStatus());
		$this->assertSame(1, $task->getPoints());
		$this->assertSame('PHID-USER-1337', $task->getAssigneePHID());
		$this->assertFalse($task->isClosed());
	}

	public function testCanSetAndGetAssignee()
	{
		$task = new Task($this->newDomainTask(), 'doing', Task::OPEN_TASK, 1);
		$assignee = 'Meh';
		$task->setAssigneeName($assignee);
		$this->assertSame($assignee, $task->getAssigneeName());
	}

	public function testCanSetAndGetCssClass()
	{
		$task = new Task($this->newDomainTask(), 'doing', Task::OPEN_TASK, 1);
		$class = 'foo';
		$task->setCssClass($class);
		$this->assertSame($class, $task->getCssClass());
	}
}
