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
			'transactionType' => 'projectcolumn',
			'oldValue' => [
				'columnPHIDs' => ['PHID-123todo'],
				'projectPHID' => 'PHID-666',
			],
			'newValue' => [
				'columnPHIDs' => ['PHID-123done'],
				'projectPHID' => 'PHID-666',
			],
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
			'transactionType' => 'projectcolumn',
			'oldValue' => [
				'columnPHIDs' => ['PHID-123todo'],
				'projectPHID' => 'PHID-123',
			],
			'newValue' => [
				'columnPHIDs' => ['PHID-123done'],
				'projectPHID' => 'PHID-123',
			],
		]));
	}

	public function testWhenMovingFromNotClosedToNotClosedColumn_isClosingTransactionReturnsFalse()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done']);
		$this->assertFalse($dispatcher->isClosingTransaction([
			'transactionType' => 'projectcolumn',
			'oldValue' => [
				'columnPHIDs' => ['PHID-123todo'],
				'projectPHID' => 'PHID-123',
			],
			'newValue' => [
				'columnPHIDs' => ['PHID-123doing'],
				'projectPHID' => 'PHID-123',
			],
		]));
	}

	public function testWhenMovingFromUnspecifiedColumnToClosedColumn_isClosingTransactionReturnsTrue()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done']);
		$this->assertTrue($dispatcher->isClosingTransaction([
			'transactionType' => 'projectcolumn',
			'oldValue' => [
				'columnPHIDs' => [],
				'projectPHID' => 'PHID-123',
			],
			'newValue' => [
				'columnPHIDs' => ['PHID-123done'],
				'projectPHID' => 'PHID-123',
			],
		]));
	}

	public function testWhenMovingFromUnspecifiedColumnToNotClosedColumn_isClosingTransactionReturnsFalse()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done']);
		$this->assertFalse($dispatcher->isClosingTransaction([
			'transactionType' => 'projectcolumn',
			'oldValue' => [
				'columnPHIDs' => [],
				'projectPHID' => 'PHID-123',
			],
			'newValue' => [
				'columnPHIDs' => ['PHID-123todo'],
				'projectPHID' => 'PHID-123',
			],
		]));
	}

	public function testWhenMovingBetweenClosedColumns_isClosingTransactionReturnsFalse()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done','PHID-123wontfix']);
		$this->assertFalse($dispatcher->isClosingTransaction([
			'transactionType' => 'projectcolumn',
			'oldValue' => [
				'columnPHIDs' => ['PHID-123done'],
				'projectPHID' => 'PHID-123',
			],
			'newValue' => [
				'columnPHIDs' => ['PHID-123wontfix'],
				'projectPHID' => 'PHID-123',
			],
		]));
	}

	public function testWhenMovingBetweenNotClosedColumns_isClosingTransactionReturnsFalse()
	{
		$dispatcher = new ClosedTimeByWorkboardDispatcher('PHID-123', ['PHID-123done']);
		$this->assertFalse($dispatcher->isClosingTransaction([
			'transactionType' => 'projectcolumn',
			'oldValue' => [
				'columnPHIDs' => ['PHID-123todo'],
				'projectPHID' => 'PHID-123',
			],
			'newValue' => [
				'columnPHIDs' => ['PHID-123doing'],
				'projectPHID' => 'PHID-123',
			],
		]));
	}

}
