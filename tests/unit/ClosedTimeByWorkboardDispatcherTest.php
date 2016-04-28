<?php

use Phragile\ClosedTimeByWorkboardDispatcher;

/**
 * @covers Phragile\ClosedTimeByWorkboardDispatcher
 */
class ClosedTimeByWorkboardDispatcherTest extends PHPUnit_Framework_TestCase {

	public function testGivenColumnTransactionOfDifferentProject_isClosingTransactionReturnsFalse()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done']);
		$this->assertFalse($dispatcher->isClosingTransaction([
			'transactionType' => 'core:columns',
			'newValue' => [[
				'fromColumnPHIDs' => ['PHID-123todo' => 'PHID-123todo'],
				'columnPHID' => 'PHID-123done',
				'boardPHID' => 'PHID-666',
			]],
		]));
	}

	public function testGivenNonColumnRelatedTransaction_isClosingTransactionReturnsFalse()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done']);
		$this->assertFalse($dispatcher->isClosingTransaction([
			'transactionType' => 'status',
			'oldValue' => '',
			'newValue' => 'open',
		]));
	}

	public function testWhenMovingFromNotClosedToClosedColumn_isClosingTransactionReturnsTrue()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done']);
		$this->assertTrue($dispatcher->isClosingTransaction([
			'transactionType' => 'core:columns',
			'newValue' => [[
				'fromColumnPHIDs' => ['PHID-123todo' => 'PHID-123todo'],
				'columnPHID' => 'PHID-123done',
				'boardPHID' => 'PHID-123',
			]],
		]));
	}

	public function testWhenMovingFromNotClosedToNotClosedColumn_isClosingTransactionReturnsFalse()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done']);
		$this->assertFalse($dispatcher->isClosingTransaction([
			'transactionType' => 'core:columns',
			'newValue' => [[
				'fromColumnPHIDs' => ['PHID-123todo' => 'PHID-123todo'],
				'columnPHID' => 'PHID-123doing',
				'boardPHID' => 'PHID-123',
			]],
		]));
	}

	public function testWhenMovingFromUnspecifiedColumnToClosedColumn_isClosingTransactionReturnsTrue()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done']);
		$this->assertTrue($dispatcher->isClosingTransaction([
			'transactionType' => 'core:columns',
			'newValue' => [[
				'fromColumnPHIDs' => [],
				'columnPHID' => 'PHID-123done',
				'boardPHID' => 'PHID-123',
			]],
		]));
	}

	public function testWhenMovingFromUnspecifiedColumnToNotClosedColumn_isClosingTransactionReturnsFalse()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done']);
		$this->assertFalse($dispatcher->isClosingTransaction([
			'transactionType' => 'core:columns',
			'newValue' => [[
				'fromColumnPHIDs' => [],
				'columnPHID' => 'PHID-123todo',
				'boardPHID' => 'PHID-123',
			]],
		]));
	}

	public function testWhenMovingBetweenClosedColumns_isClosingTransactionReturnsFalse()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done','PHID-123wontfix']);
		$this->assertFalse($dispatcher->isClosingTransaction([
			'transactionType' => 'core:columns',
			'newValue' => [[
				'fromColumnPHIDs' => ['PHID-123done' => 'PHID-123done'],
				'columnPHID' => 'PHID-123wontfix',
				'boardPHID' => 'PHID-123',
			]],
		]));
	}

	public function testWhenMovingBetweenNotClosedColumns_isClosingTransactionReturnsFalse()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done']);
		$this->assertFalse($dispatcher->isClosingTransaction([
			'transactionType' => 'core:columns',
			'newValue' => [[
				'fromColumnPHIDs' => ['PHID-123todo' => 'PHID-123todo'],
				'columnPHID' => 'PHID-123doing',
				'boardPHID' => 'PHID-123',
			]],
		]));
	}

}
