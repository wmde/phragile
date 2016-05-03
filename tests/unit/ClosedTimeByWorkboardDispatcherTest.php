<?php

use Phragile\ClosedTimeByWorkboardDispatcher;
use Phragile\ColumnChangeTransaction;
use Phragile\StatusChangeTransaction;

/**
 * @covers Phragile\ClosedTimeByWorkboardDispatcher
 */
class ClosedTimeByWorkboardDispatcherTest extends PHPUnit_Framework_TestCase {

	public function testGivenColumnTransactionOfDifferentProject_isClosingTransactionReturnsFalse()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done']);
		$this->assertFalse($dispatcher->isClosingTransaction(new ColumnChangeTransaction(
			'1451638800',
			'PHID-666',
			'PHID-123todo',
			'PHID-123done'
		)));
	}

	public function testGivenNonColumnRelatedTransaction_isClosingTransactionReturnsFalse()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done']);
		$this->assertFalse($dispatcher->isClosingTransaction(new StatusChangeTransaction(
			'1451638800',
			'', // TODO: shouldn't this be null?
			'open'
		)));
	}

	public function testWhenMovingFromNotClosedToClosedColumn_isClosingTransactionReturnsTrue()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done']);
		$this->assertTrue($dispatcher->isClosingTransaction(new ColumnChangeTransaction(
			'1451638800',
			'PHID-123',
			'PHID-123todo',
			'PHID-123done'
		)));
	}

	public function testWhenMovingFromNotClosedToNotClosedColumn_isClosingTransactionReturnsFalse()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done']);
		$this->assertFalse($dispatcher->isClosingTransaction(new ColumnChangeTransaction(
			'1451638800',
			'PHID-123',
			'PHID-123todo',
			'PHID-123doing'
		)));
	}

	public function testWhenMovingFromUnspecifiedColumnToClosedColumn_isClosingTransactionReturnsTrue()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done']);
		$this->assertTrue($dispatcher->isClosingTransaction(new ColumnChangeTransaction(
			'1451638800',
			'PHID-123',
			false,
			'PHID-123done'
		)));
	}

	public function testWhenMovingFromUnspecifiedColumnToNotClosedColumn_isClosingTransactionReturnsFalse()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done']);
		$this->assertFalse($dispatcher->isClosingTransaction(new ColumnChangeTransaction(
			'1451638800',
			'PHID-123',
			false,
			'PHID-123todo'
		)));
	}

	public function testWhenMovingBetweenClosedColumns_isClosingTransactionReturnsFalse()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done','PHID-123wontfix']);
		$this->assertFalse($dispatcher->isClosingTransaction(new ColumnChangeTransaction(
			'1451638800',
			'PHID-123',
			'PHID-123done',
			'PHID-123wontfix'
		)));
	}

	public function testWhenMovingBetweenNotClosedColumns_isClosingTransactionReturnsFalse()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done']);
		$this->assertFalse($dispatcher->isClosingTransaction(new ColumnChangeTransaction(
			'1451638800',
			'PHID-123',
			'PHID-123todo',
			'PHID-123doing'
		)));
	}

}
