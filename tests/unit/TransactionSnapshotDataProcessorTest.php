<?php

use Phragile\ColumnChangeTransaction;
use Phragile\MergedIntoTransaction;
use Phragile\StatusChangeTransaction;
use Phragile\TransactionSnapshotDataProcessor;

/**
 * @covers Phragile\TransactionSnapshotDataProcessor
 */
class TransactionSnapshotDataProcessorTest extends PHPUnit_Framework_TestCase {

	public function testProcessConvertsTransactionsOfKnownTypes()
	{
		$snapshotData = [
			'fooTask' => [
				[
					'timestamp' => '1451638800', // 01.01.2016 10:00:00
					'type' => 'columnChange',
					'workboardPHID' => 'PHID-PROJ-123',
					'oldColumnPHID' => 'PHID-PCOL-123',
					'newColumnPHID' => 'PHID-PCOL-456',
				],
				[
					'timestamp' => '1451642400', // 01.01.2016 11:00:00
					'type' => 'statusChange',
					'oldStatus' => 'open',
					'newStatus' => 'resolved'
				],
			],
			'bazTask' => [
				[
					'timestamp' => '1451646000', // 01.01.2016 12:00:00
					'type' => 'mergedInto'
				],
			],
		];

		$processor = new TransactionSnapshotDataProcessor();
		$transactions = $processor->process($snapshotData);

		$this->assertEquals([
				'fooTask' => [
					new ColumnChangeTransaction([
						'timestamp' => '1451638800',
						'workboardPHID' => 'PHID-PROJ-123',
						'oldColumnPHID' => 'PHID-PCOL-123',
						'newColumnPHID' => 'PHID-PCOL-456',
					]),
					new StatusChangeTransaction([
						'timestamp' => '1451642400',
						'oldStatus' => 'open',
						'newStatus' => 'resolved',
					])
				],
				'bazTask' => [
					new MergedIntoTransaction('1451646000')
				]
			], $transactions
		);
	}

	public function testGivenNoTypeKey_snapshotTransactionIsIgnored()
	{
		$snapshotData = [
			'fooTask' => [
				[
					'timestamp' => '1451638800', // 01.01.2016 10:00:00
					'workboardPHID' => 'PHID-PROJ-123',
					'oldColumnPHID' => 'PHID-PCOL-123',
					'newColumnPHID' => 'PHID-PCOL-456',
				],
			]
		];

		$processor = new TransactionSnapshotDataProcessor();
		$transactions = $processor->process($snapshotData);

		$this->assertEquals(['fooTask' => []], $transactions);
	}

	public function testGivenUnknownTypeKey_snapshotTransactionIsIgnored()
	{
		$snapshotData = [
			'fooTask' => [
				[
					'timestamp' => '1451638800', // 01.01.2016 10:00:00
					'type' => 'someReallyReallyOddTransaction',
				],
			]
		];

		$processor = new TransactionSnapshotDataProcessor();
		$transactions = $processor->process($snapshotData);

		$this->assertEquals(['fooTask' => []], $transactions);
	}

}
