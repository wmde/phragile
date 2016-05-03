<?php

use Phragile\ColumnChangeTransaction;
use Phragile\MergedIntoTransaction;
use Phragile\StatusChangeTransaction;
use Phragile\TransactionRawDataProcessor;

/**
 * @covers Phragile\TransactionRawDataProcessor
 */
class TransactionRawDataProcessorTest extends PHPUnit_Framework_TestCase {

	public function testProcessColumnChangeTransaction()
	{
		$rawData = [
			'taskFoo' => [[
				'dateCreated' => '1451638800', // 01.01.2016 10:00:00
				'transactionType' => 'core:columns',
				'newValue' => [[
					'boardPHID' => 'PHID-PROJ-123',
					'columnPHID' => 'PHID-PCOL-456',
					'fromColumnPHIDs' => ['PHID-PCOL-123']
				]]
			]]
		];

		$processor = new TransactionRawDataProcessor();
		$transactions = $processor->process($rawData);

		$this->assertCount(1, $transactions);
		$this->assertArrayHasKey('taskFoo', $transactions);

		$this->assertCount(1, $transactions['taskFoo']);
		$this->assertInstanceOf(ColumnChangeTransaction::class, $transactions['taskFoo'][0]);
		$this->assertEquals('1451638800', $transactions['taskFoo'][0]->getTimestamp());
		$this->assertEquals('PHID-PROJ-123', $transactions['taskFoo'][0]->getWorkboardPHID());
		$this->assertEquals('PHID-PCOL-123', $transactions['taskFoo'][0]->getOldColumnPHID());
		$this->assertEquals('PHID-PCOL-456', $transactions['taskFoo'][0]->getNewColumnPHID());
	}

	public function testProcessStatusChangeTransaction()
	{
		$rawData = [
			'taskFoo' => [[
				'dateCreated' => '1451638800', // 01.01.2016 10:00:00
				'transactionType' => 'status',
				'oldValue' => 'open',
				'newValue' => 'resolved',
			]]
		];

		$processor = new TransactionRawDataProcessor();
		$transactions = $processor->process($rawData);

		$this->assertCount(1, $transactions);
		$this->assertArrayHasKey('taskFoo', $transactions);

		$this->assertCount(1, $transactions['taskFoo']);
		$this->assertInstanceOf(StatusChangeTransaction::class, $transactions['taskFoo'][0]);
		$this->assertEquals('1451638800', $transactions['taskFoo'][0]->getTimestamp());
		$this->assertEquals('open', $transactions['taskFoo'][0]->getOldStatus());
		$this->assertEquals('resolved', $transactions['taskFoo'][0]->getNewStatus());
	}

	public function testProcessMergedIntoTransaction()
	{
		$rawData = [
			'taskFoo' => [[
				'dateCreated' => '1451638800', // 01.01.2016 10:00:00
				'transactionType' => 'mergedinto',
			]]
		];

		$processor = new TransactionRawDataProcessor();
		$transactions = $processor->process($rawData);

		$this->assertCount(1, $transactions);
		$this->assertArrayHasKey('taskFoo', $transactions);

		$this->assertCount(1, $transactions['taskFoo']);
		$this->assertInstanceOf(MergedIntoTransaction::class, $transactions['taskFoo'][0]);
		$this->assertEquals('1451638800', $transactions['taskFoo'][0]->getTimestamp());
	}

	public function testGivenNoTransactionType_processSkipsTransactionData()
	{
		$rawData = [
			'taskFoo' => [[
				'newValue' => 'someValue'
			]]
		];

		$processor = new TransactionRawDataProcessor();
		$transactions = $processor->process($rawData);

		$this->assertEmpty($transactions['taskFoo']);
	}

}
