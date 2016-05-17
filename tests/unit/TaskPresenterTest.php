<?php

namespace Phragile\Tests;

use Phragile\StatusByStatusFieldDispatcher;
use Phragile\StatusDispatcher;
use Phragile\TaskPresenter;
use Phragile\Domain\Task as DomainTask;
use Phragile\Presentation\Task as PresentationTask;

/**
 * @covers Phragile\TaskPresenter
 */
class TaskPresenterTest extends \PHPUnit_Framework_TestCase {

	public function testCreatesPresentationTasks()
	{
		$openTask = new DomainTask([
			'id' => 42,
			'title' => 'A task',
			'status' => 'open',
			'priority' => 'High',
			'points' => 3,
			'projectPHIDs' => ['PHID-PROJ-001'],
			'assigneePHID' => 'PHID-USER-1337',
		]);
		$tasks = [$openTask];
		$presenter = new TaskPresenter(
			new StatusByStatusFieldDispatcher('PHID-PROJ-010'),
			['ignored_columns' => [], 'ignore_estimates' => false]
		);
		$this->assertEquals(
			[
				new PresentationTask(
					$openTask,
					'doing',
					PresentationTask::OPEN_TASK,
					3
				)
			],
			$presenter->render($tasks)
		);
	}

	public function testTaskStatusResolved()
	{
		$tasks = [
			new DomainTask([
				'id' => 42,
				'title' => 'A task',
				'status' => 'resolved',
				'priority' => 'High',
				'points' => 3,
				'projectPHIDs' => ['PHID-PROJ-001'],
				'assigneePHID' => null,
			]),
		];
		$presenter = new TaskPresenter(
			new StatusByStatusFieldDispatcher('PHID-PROJ-010'),
			['ignored_columns' => [], 'ignore_estimates' => false]
		);
		$presentationTasks = $presenter->render($tasks);
		$this->assertCount(1, $presentationTasks);
		$presentationTask = $presentationTasks[0];
		$this->assertEquals('resolved', $presentationTask->getStatus());
		$this->assertTrue($presentationTask->isClosed());
	}

	public function testTaskStatusPatchToReview()
	{
		$tasks = [
			new DomainTask([
				'id' => 42,
				'title' => 'A task',
				'status' => 'open',
				'priority' => 'High',
				'points' => 3,
				'projectPHIDs' => ['PHID-PROJ-010'],
				'assigneePHID' => 'PHID-USER-1337',
			]),
		];
		$presenter = new TaskPresenter(
			new StatusByStatusFieldDispatcher('PHID-PROJ-010'),
			['ignored_columns' => [], 'ignore_estimates' => false]
		);
		$presentationTasks = $presenter->render($tasks);
		$this->assertCount(1, $presentationTasks);
		$presentationTask = $presentationTasks[0];
		$this->assertEquals('patch to review', $presentationTask->getStatus());
		$this->assertFalse($presentationTask->isClosed());
	}

	public function testIgnoreEstimates()
	{
		$tasks = [
			new DomainTask([
				'id' => 42,
				'title' => 'A task',
				'status' => 'open',
				'priority' => 'High',
				'points' => 3,
				'projectPHIDs' => ['PHID-PROJ-001'],
				'assigneePHID' => 'PHID-USER-1337',
			]),
		];
		$presenter = new TaskPresenter(
			new StatusByStatusFieldDispatcher('PHID-PROJ-010'),
			['ignored_columns' => [], 'ignore_estimates' => true]
		);
		$presentationTasks = $presenter->render($tasks);
		$this->assertCount(1, $presentationTasks);
		$presentationTask = $presentationTasks[0];
		$this->assertEquals(1, $presentationTask->getPoints());
	}

	public function testIgnoredColumns()
	{
		$tasks = [
			new DomainTask([
				'id' => 42,
				'title' => 'A task',
				'status' => 'open',
				'priority' => 'High',
				'points' => 3,
				'projectPHIDs' => ['PHID-PROJ-001'],
				'assigneePHID' => 'PHID-USER-1337',
			]),
		];

		$mockStatusDispatcher = $this->getMockBuilder( StatusDispatcher::class )
			->disableOriginalConstructor()
			->getMock();
		$mockStatusDispatcher->method('getStatus')->willReturn('PHID-PCOL-123ign');

		$presenter = new TaskPresenter(
			$mockStatusDispatcher,
			['ignored_columns' => ['PHID-PCOL-123ign'], 'ignore_estimates' => false]
		);
		$this->assertEmpty($presenter->render($tasks));
	}
}
