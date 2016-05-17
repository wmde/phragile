<?php

namespace Phragile\Tests;

use Phragile\StatusByStatusFieldDispatcher;
use Phragile\Domain\Task;

class StatusByStatusFieldDispatcherTest extends TestCase {

	private function newTask(array $attributes)
	{
		$defaults = [
			'id' => 101,
			'title' => 'A Test Task',
			'priority' => 'Normal',
			'status' => 'open',
			'points' => 1,
			'projectPHIDs' => [],
			'assigneePHID' => null,
		];
		return new Task(array_merge($defaults, $attributes));
	}

	public function taskWithCorrectStatusProvider()
	{
		return [
			[
				$this->newTask(['status' => 'open', 'projectPHIDs' => [],'assigneePHID' => null]),
				'open',
				false
			],
			[

				$this->newTask(['status' => 'resolved', 'projectPHIDs' => [],'assigneePHID' => null]),
				'resolved',
				true
			],
			[
				$this->newTask(['status' => 'resolved', 'projectPHIDs' => [$this->reviewTagPHID],'assigneePHID' => null]),
				'resolved',
				true
			],
			[
				$this->newTask(['status' => 'open', 'projectPHIDs' => [$this->reviewTagPHID],'assigneePHID' => null]),
				'patch to review',
				false
			],
		];
	}

	private $reviewTagPHID = 'PHID-PROJ-1337';

	/**
	 * @dataProvider taskWithCorrectStatusProvider
	 */
	public function testReturnsCorrectStatus($rawTask, $status, $isClosed)
	{
		$statusDispatcher = new StatusByStatusFieldDispatcher($this->reviewTagPHID);

		$this->assertSame($status, $statusDispatcher->getStatus($rawTask));
		$this->assertSame($isClosed, $statusDispatcher->isClosed($rawTask));
	}
}
