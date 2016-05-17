<?php

namespace Phragile\Tests;

use Phragile\ClosedTimeByWorkboardDispatcher;
use Phragile\Domain\ColumnChangeTransaction;
use Phragile\Domain\StatusChangeTransaction;

/**
 * @covers Phragile\ClosedTimeByWorkboardDispatcher
 */
class ClosedTimeByWorkboardDispatcherTest extends \PHPUnit_Framework_TestCase {

	public function testGivenColumnTransactionOfDifferentProject_isClosingTransactionReturnsFalse()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done']);
		$this->assertFalse($dispatcher->isClosingTransaction(new ColumnChangeTransaction([
			'timestamp' => '1451638800',
			'workboardPHID' => 'PHID-666',
			'oldColumnPHID' => 'PHID-123todo',
			'newColumnPHID' => 'PHID-123done',
		])));
	}

	public function testGivenNonColumnRelatedTransaction_isClosingTransactionReturnsFalse()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done']);
		$this->assertFalse($dispatcher->isClosingTransaction(new StatusChangeTransaction([
			'timestamp' => '1451638800',
			'oldStatus' => null,
			'newStatus' => 'open',
		])));
	}

	public function testWhenMovingFromNotClosedToClosedColumn_isClosingTransactionReturnsTrue()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done']);
		$this->assertTrue($dispatcher->isClosingTransaction(new ColumnChangeTransaction([
			'timestamp' => '1451638800',
			'workboardPHID' => 'PHID-123',
			'oldColumnPHID' => 'PHID-123todo',
			'newColumnPHID' => 'PHID-123done',
		])));
	}

	public function testWhenMovingFromNotClosedToNotClosedColumn_isClosingTransactionReturnsFalse()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done']);
		$this->assertFalse($dispatcher->isClosingTransaction(new ColumnChangeTransaction([
			'timestamp' => '1451638800',
			'workboardPHID' => 'PHID-123',
			'oldColumnPHID' => 'PHID-123todo',
			'newColumnPHID' => 'PHID-123doing',
		])));
	}

	public function testWhenMovingFromUnspecifiedColumnToClosedColumn_isClosingTransactionReturnsTrue()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done']);
		$this->assertTrue($dispatcher->isClosingTransaction(new ColumnChangeTransaction([
			'timestamp' => '1451638800',
			'workboardPHID' => 'PHID-123',
			'oldColumnPHID' => false,
			'newColumnPHID' => 'PHID-123done',
		])));
	}

	public function testWhenMovingFromUnspecifiedColumnToNotClosedColumn_isClosingTransactionReturnsFalse()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done']);
		$this->assertFalse($dispatcher->isClosingTransaction(new ColumnChangeTransaction([
			'timestamp' => '1451638800',
			'workboardPHID' => 'PHID-123',
			'oldColumnPHID' => false,
			'newColumnPHID' => 'PHID-123todo',
		])));
	}

	public function testWhenMovingBetweenClosedColumns_isClosingTransactionReturnsFalse()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done','PHID-123wontfix']);
		$this->assertFalse($dispatcher->isClosingTransaction(new ColumnChangeTransaction([
			'timestamp' => '1451638800',
			'workboardPHID' => 'PHID-123',
			'oldColumnPHID' => 'PHID-123done',
			'newColumnPHID' => 'PHID-123wontfix',
		])));
	}

	public function testWhenMovingBetweenNotClosedColumns_isClosingTransactionReturnsFalse()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done']);
		$this->assertFalse($dispatcher->isClosingTransaction(new ColumnChangeTransaction([
			'timestamp' => '1451638800',
			'workboardPHID' => 'PHID-123',
			'oldColumnPHID' => 'PHID-123todo',
			'newColumnPHID' => 'PHID-123doing',
		])));
	}

}
