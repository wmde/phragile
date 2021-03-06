<?php

use App\Console\Commands\Lib\SnapshotTransactionDataConverter;

/**
 * @covers App\Console\Commands\Lib\SnapshotTransactionDataConverter
 */
class SnapshotTransactionDataConverterTest extends PHPUnit_Framework_TestCase {

	private function getPhabricatorTransactions()
	{
		return [
			'10' => [
				[
					'taskID' => '10',
					'transactionID' => '574',
					'transactionPHID' => 'PHID-XACT-TASK-thuitevwbsvzq3w',
					'transactionType' => 'core:columns',
					'oldValue' => null,
					'newValue' => [
						[
							'columnPHID' => 'PHID-PCOL-4hyhg5eihidfzexjxo6l',
							'boardPHID' => 'PHID-PROJ-we3kvpoegtfzytlzsbq5',
							'fromColumnPHIDs' => [],
						]
					],
					'comments' => null,
					'authorPHID' => 'PHID-USER-4evwbszqu47ukghwqpyo',
					'dateCreated' => '1461843396',
				],
				[
					'taskID' => '10',
					'transactionID' => '615',
					'transactionPHID' => 'PHID-XACT-TASK-thuit5jaapizdv3',
					'transactionType' => 'core:columns',
					'oldValue' => null,
					'newValue' => [
						[
							'columnPHID' => 'PHID-PCOL-babqxkbnh75r3dyxmuys',
							'boardPHID' => 'PHID-PROJ-we3kvpoegtfzytlzsbq5',
							'fromColumnPHIDs' => [
								'PHID-PCOL-4hyhg5eihidfzexjxo6l' => 'PHID-PCOL-4hyhg5eihidfzexjxo6l',
							],
						]
					],
					'comments' => null,
					'authorPHID' => 'PHID-USER-4evwbszqu47ukghwqpyo',
					'dateCreated' => '1461852396',
				],
				[
					'taskID' => '10',
					'transactionID' => '85',
					'transactionPHID' => 'PHID-XACT-TASK-fa4mch7cmvs56yp',
					'transactionType' => 'status',
					'oldValue' => null,
					'newValue' => 'open',
					'comments' => null,
					'authorPHID' => 'PHID-USER-4evwbszqu47ukghwqpyo',
					'dateCreated' => '1436877423',
				],
				[
					'taskID' => '10',
					'transactionID' => '87',
					'transactionPHID' => 'PHID-XACT-TASK-gnhol6bzawe4fgi',
					'transactionType' => 'mergedinto',
					'oldValue' => null,
					'newValue' => 'PHID-TASK-kbqbuddlt65redxoce6g',
					'comments' => null,
					'authorPHID' => 'PHID-USER-4evwbszqu47ukghwqpyo',
					'dateCreated' => '1462366409',
				]
			]
		];
	}

	private function getPhabricatorPre2016Week15Transactions()
	{
		return [
			'10' => [
				'0' => [
					'taskID' => '10',
					'transactionID' => '574',
					'transactionPHID' => 'PHID-XACT-TASK-thuitevwbsvzq3w',
					'transactionType' => 'projectcolumn',
					'oldValue' => [
						'columnPHIDs' => [],
						'projectPHID' => 'PHID-PROJ-we3kvpoegtfzytlzsbq5',
					],
					'newValue' => [
						'columnPHIDs' => ['PHID-PCOL-4hyhg5eihidfzexjxo6l'],
						'projectPHID' => 'PHID-PROJ-we3kvpoegtfzytlzsbq5',
						'afterPHID' => null,
						'beforePHID' => null,
					],
					'comments' => null,
					'authorPHID' => 'PHID-USER-4evwbszqu47ukghwqpyo',
					'dateCreated' => '1461843396',
				],
				'1' => [
					'taskID' => '10',
					'transactionID' => '615',
					'transactionPHID' => 'PHID-XACT-TASK-thuit5jaapizdv3',
					'transactionType' => 'projectcolumn',
					'oldValue' => [
						'columnPHIDs' => ['PHID-PCOL-4hyhg5eihidfzexjxo6l'],
						'projectPHID' => 'PHID-PROJ-we3kvpoegtfzytlzsbq5',
					],
					'newValue' => [
						'columnPHIDs' => ['PHID-PCOL-babqxkbnh75r3dyxmuys'],
						'projectPHID' => 'PHID-PROJ-we3kvpoegtfzytlzsbq5',
						'afterPHID' => null,
						'beforePHID' => null,
					],
					'comments' => null,
					'authorPHID' => 'PHID-USER-4evwbszqu47ukghwqpyo',
					'dateCreated' => '1461852396',
				],
				'2' => [
					'taskID' => '10',
					'transactionID' => '85',
					'transactionPHID' => 'PHID-XACT-TASK-fa4mch7cmvs56yp',
					'transactionType' => 'status',
					'oldValue' => null,
					'newValue' => 'open',
					'comments' => null,
					'authorPHID' => 'PHID-USER-4evwbszqu47ukghwqpyo',
					'dateCreated' => '1436877423',
				],
				'3' => [
					'taskID' => '10',
					'transactionID' => '87',
					'transactionPHID' => 'PHID-XACT-TASK-gnhol6bzawe4fgi',
					'transactionType' => 'mergedinto',
					'oldValue' => null,
					'newValue' => 'PHID-TASK-kbqbuddlt65redxoce6g',
					'comments' => null,
					'authorPHID' => 'PHID-USER-4evwbszqu47ukghwqpyo',
					'dateCreated' => '1462366409',
				]
			]
		];
	}

	private function getConvertedTransactions()
	{
		return [
			'10' => [
				[
					'type' => 'columnChange',
					'timestamp' => '1461843396',
					'workboardPHID' => 'PHID-PROJ-we3kvpoegtfzytlzsbq5',
					'oldColumnPHID' => false,
					'newColumnPHID' => 'PHID-PCOL-4hyhg5eihidfzexjxo6l',
				],
				[
					'type' => 'columnChange',
					'timestamp' => '1461852396',
					'workboardPHID' => 'PHID-PROJ-we3kvpoegtfzytlzsbq5',
					'oldColumnPHID' => 'PHID-PCOL-4hyhg5eihidfzexjxo6l',
					'newColumnPHID' => 'PHID-PCOL-babqxkbnh75r3dyxmuys',
				],
				[
					'type' => 'statusChange',
					'timestamp' => '1436877423',
					'oldStatus' => null,
					'newStatus' => 'open',
				],
				[
					'type' => 'mergeAndClose',
					'timestamp' => '1462366409',
				]
			]
		];
	}

	public function testGivenPhabricatorJsonTransactions_needsConversionReturnsTrue()
	{
		$converter = new SnapshotTransactionDataConverter();
		$this->assertTrue($converter->needsConversion($this->getPhabricatorTransactions()));
	}

	public function testGivenConvertedTransactions_needsConversionReturnsFalse()
	{
		$converter = new SnapshotTransactionDataConverter();
		$this->assertFalse($converter->needsConversion($this->getConvertedTransactions()));
	}

	public function testGivenTransactionDataInUnknownFormat_needsConversionReturnsFalse()
	{
		$converter = new SnapshotTransactionDataConverter();
		$this->assertFalse($converter->needsConversion(['10' => [['foo' => 'bar']]]));
	}

	public function testGivenPhabricatorJsonTransactions_convertReturnsConvertedData()
	{
		$expectedConvertedData = $this->getConvertedTransactions();
		$converter = new SnapshotTransactionDataConverter();
		$this->assertSame($expectedConvertedData, $converter->convert($this->getPhabricatorTransactions()));
	}

	public function testGivenConvertedTransactions_convertReturnsUnchangedData()
	{
		$expectedConvertedData = $this->getConvertedTransactions();
		$converter = new SnapshotTransactionDataConverter();
		$this->assertSame($expectedConvertedData, $converter->convert($this->getConvertedTransactions()));
	}

	public function testGivenPhabricatorJsonPre2016Week15Transactions_needsConversionReturnsTrue()
	{
		$converter = new SnapshotTransactionDataConverter();
		$this->assertTrue($converter->needsConversion($this->getPhabricatorPre2016Week15Transactions()));
	}

	public function testGivenPhabricatorJsonPre2016Week15Transactions_convertReturnsConvertedData()
	{
		$expectedConvertedData = $this->getConvertedTransactions();
		$converter = new SnapshotTransactionDataConverter();
		$this->assertSame($expectedConvertedData, $converter->convert($this->getPhabricatorPre2016Week15Transactions()));
	}

	public function testGivenNoTransactionForTask_convertReturnsUnchangedData()
	{
		$converter = new SnapshotTransactionDataConverter();
		$this->assertSame(['11' => []], $converter->convert(['11' => []]));
	}

}
