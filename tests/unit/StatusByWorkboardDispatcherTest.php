<?php

use Phragile\ColumnChangeTransaction;
use Phragile\PhabricatorAPI;
use Phragile\ProjectColumnRepository;
use Phragile\StatusByWorkboardDispatcher;
use Phragile\SortedTransactionList;

/**
 * @covers Phragile\StatusByWorkboardDispatcher
 */
class StatusByWorkboardDispatcherTest extends TestCase {

	private $workboardColumns = [
		'PHID-123abc' => 'backlog',
		'PHID-321cba' => 'done',
		'PHID-abc123' => 'to do',
	];

	private function newPhabricatorAPI()
	{
		$phabricatorAPI = $this->getMockBuilder(PhabricatorAPI::class)
			->disableOriginalConstructor()
			->getMock();
		$phabricatorAPI->method('queryPHIDs')->will($this->returnCallback(function()
		{
			return array_map(function($column)
			{
				return ['name' => $column];
			}, $this->workboardColumns);
		}));
		return $phabricatorAPI;
	}

	private function newSprint()
	{
		$sprint = new Sprint([
			'phid' => 'PHID-123',
		]);
		$sprint->project = new Project([
			'closed_statuses' => 'done',
			'default_column' => 'backlog',
		]);
		return $sprint;
	}

	private function newDispatcher(Sprint $sprint, array $transactions)
	{
		return new StatusByWorkboardDispatcher(
			$sprint,
			new SortedTransactionList($transactions),
			new ProjectColumnRepository(
				'PHID-123',
				$transactions,
				$this->newPhabricatorAPI()
			)
		);
	}

	public function testGivenNoTransactionForTask_tasksStatusIsBasedOnProjectsDefaultColumn()
	{
		$sprint = $this->newSprint();
		$task = ['id' => 'fooTask'];
		$transactions = [];
		$dispatcher = $this->newDispatcher($sprint, $transactions);
		$this->assertEquals('backlog', $dispatcher->getStatus($task));
		$this->assertFalse($dispatcher->isClosed($task));
	}

	public function testGivenSingleTransactionForTask_tasksStatusIsBasedOnThatTransaction()
	{
		$sprint = $this->newSprint();
		$task = ['id' => 'fooTask'];
		$transactions = [];
		$transactions['fooTask'][] = $this->getFirstTransaction();
		$dispatcher = $this->newDispatcher($sprint, $transactions);
		$this->assertEquals('done', $dispatcher->getStatus($task));
		$this->assertTrue($dispatcher->isClosed($task));
	}

	public function testGivenTwoTransactionsForTask_tasksStatusIsBasedOnTheMostRecentTransaction()
	{
		$sprint = $this->newSprint();
		$task = ['id' => 'fooTask'];
		$transactions = [];
		$transactions['fooTask'][] = $this->getFirstTransaction();
		$transactions['fooTask'][] = $this->getSecondTransaction();
		$dispatcher = $this->newDispatcher($sprint, $transactions);
		$this->assertEquals('to do', $dispatcher->getStatus($task));
		$this->assertFalse($dispatcher->isClosed($task));
	}

	public function testGivenThreeTransactionsForTask_tasksStatusIsBasedOnTheMostRecentTransaction()
	{
		$sprint = $this->newSprint();
		$task = ['id' => 'fooTask'];
		$transactions = [];
		$transactions['fooTask'][] = $this->getFirstTransaction();
		$transactions['fooTask'][] = $this->getSecondTransaction();
		$transactions['fooTask'][] = $this->getThirdTransaction();
		$dispatcher = $this->newDispatcher($sprint, $transactions);
		$this->assertEquals('done', $dispatcher->getStatus($task));
		$this->assertTrue($dispatcher->isClosed($task));
	}

	public function testGivenIrrelevantTransaction_tasksStatusIsBasedOnProjectsDefaultColumn()
	{
		$sprint = $this->newSprint();
		$task = ['id' => 'fooTask'];
		$transaction = $this->getTransactionForAnotherProject();
		$dispatcher = $this->newDispatcher($sprint, ['fooTask' => [$transaction]]);
		$this->assertEquals('backlog', $dispatcher->getStatus($task));
		$this->assertFalse($dispatcher->isClosed($task));
	}

	private function getFirstTransaction()
	{
		return new ColumnChangeTransaction([
			'timestamp' => DateTime::createFromFormat('d.m.Y H:i:s', '01.01.2016 10:00:00')->format('U'),
			'workboardPHID' => 'PHID-123',
			'oldColumnPHID' => $this->getColumnPhid('to do'),
			'newColumnPHID' => $this->getColumnPhid('done'),
		]);
	}

	private function getSecondTransaction()
	{
		return new ColumnChangeTransaction([
			'timestamp' => DateTime::createFromFormat('d.m.Y H:i:s', '01.01.2016 12:00:00')->format('U'),
			'workboardPHID' => 'PHID-123',
			'oldColumnPHID' => $this->getColumnPhid('done'),
			'newColumnPHID' => $this->getColumnPhid('to do'),
		]);
	}

	private function getThirdTransaction()
	{
		return new ColumnChangeTransaction([
			'timestamp' => DateTime::createFromFormat('d.m.Y H:i:s', '01.01.2016 13:00:00')->format('U'),
			'workboardPHID' => 'PHID-123',
			'oldColumnPHID' => $this->getColumnPhid('to do'),
			'newColumnPHID' => $this->getColumnPhid('done'),
		]);
	}

	private function getTransactionForAnotherProject()
	{
		return new ColumnChangeTransaction([
			'timestamp' => DateTime::createFromFormat('d.m.Y H:i:s', '01.01.2016 10:00:00')->format('U'),
			'workboardPHID' => 'PHID-SOME-OTHER-PROJECT-PHID',
			'oldColumnPHID' => $this->getColumnPhid('to do'),
			'newColumnPHID' => $this->getColumnPhid('done'),
		]);
	}

	private function getColumnPhid($name)
	{
		$columnPHIDs = array_flip($this->workboardColumns);
		return $columnPHIDs[$name];
	}

}
