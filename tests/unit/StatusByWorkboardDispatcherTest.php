<?php

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
		$transaction = $this->getFirstTransaction();
		$transaction['oldValue']['projectPHID'] = 'PHID-SOME-OTHER-PROJ-PHID';
		$transaction['newValue']['projectPHID'] = 'PHID-SOME-OTHER-PROJ-PHID';
		$dispatcher = $this->newDispatcher($sprint, ['fooTask' => [$transaction]]);
		$this->assertEquals('backlog', $dispatcher->getStatus($task));
		$this->assertFalse($dispatcher->isClosed($task));
	}

	private function getFirstTransaction()
	{
		return [
			'dateCreated' => DateTime::createFromFormat('d.m.Y H:i:s', '01.01.2016 10:00:00')->format('U'),
			'transactionType' => 'projectcolumn',
			'oldValue' => [
				'columnPHIDs' => [$this->getColumnPhid('to do')],
				'projectPHID' => 'PHID-123',
			],
			'newValue' => [
				'columnPHIDs' => [$this->getColumnPhid('done')],
				'projectPHID' => 'PHID-123',
			]
		];
	}

	private function getSecondTransaction()
	{
		return [
			'dateCreated' => DateTime::createFromFormat('d.m.Y H:i:s', '01.01.2016 12:00:00')->format('U'),
			'transactionType' => 'projectcolumn',
			'oldValue' => [
				'columnPHIDs' => [$this->getColumnPhid('done')],
				'projectPHID' => 'PHID-123',
			],
			'newValue' => [
				'columnPHIDs' => [$this->getColumnPhid('to do')],
				'projectPHID' => 'PHID-123',
			]
		];
	}

	private function getThirdTransaction()
	{
		return [
			'dateCreated' => DateTime::createFromFormat('d.m.Y H:i:s', '01.01.2016 13:00:00')->format('U'),
			'transactionType' => 'projectcolumn',
			'oldValue' => [
				'columnPHIDs' => [$this->getColumnPhid('to do')],
				'projectPHID' => 'PHID-123',
			],
			'newValue' => [
				'columnPHIDs' => [$this->getColumnPhid('done')],
				'projectPHID' => 'PHID-123',
			]
		];
	}

	private function getColumnPhid($name)
	{
		$columnPHIDs = array_flip($this->workboardColumns);
		return $columnPHIDs[$name];
	}

}
