<?php

use Phragile\TransactionList;

/**
 * @covers Phragile\TransactionList
 */
class TransactionListTest extends PHPUnit_Framework_TestCase {

	public function testTaskTransactionsAreSortedByTimestamp()
	{
		$transactions = [
			'fooTask' => [
				[
					'dateCreated' => DateTime::createFromFormat('d.m.Y', '02.01.2016')->format('U'),
					'transactionType' => 'projectcolumn',
					'oldValue' => [
						'columnPHIDs' => ['PHID-doing'],
						'projectPHID' => 'PHID-PROJ-AWESOME',
					],
					'newValue' => [
						'columnPHIDs' => ['PHID-done'],
						'projectPHID' => 'PHID-PROJ-AWESOME',
					]
				],
				[
					'dateCreated' => DateTime::createFromFormat('d.m.Y', '01.01.2016')->format('U'),
					'transactionType' => 'projectcolumn',
					'oldValue' => [
						'columnPHIDs' => ['PHID-backlog'],
						'projectPHID' => 'PHID-PROJ-AWESOME',
					],
					'newValue' => [
						'columnPHIDs' => ['PHID-doing'],
						'projectPHID' => 'PHID-PROJ-AWESOME',
					]
				],
				[
					'dateCreated' => DateTime::createFromFormat('d.m.Y', '03.01.2016')->format('U'),
					'transactionType' => 'projectcolumn',
					'oldValue' => [
						'columnPHIDs' => ['PHID-doing'],
						'projectPHID' => 'PHID-PROJ-AWESOME',
					],
					'newValue' => [
						'columnPHIDs' => ['PHID-done'],
						'projectPHID' => 'PHID-PROJ-AWESOME',
					]
				],
			]
		];
		$sortedTransactions = (new TransactionList($transactions))->getChronologicallySorted();
		$this->assertEquals(
			'01.01.2016',
			DateTime::createFromFormat('U', $sortedTransactions['fooTask'][0]['dateCreated'])->format('d.m.Y')
		);
		$this->assertEquals(
			'02.01.2016',
			DateTime::createFromFormat('U', $sortedTransactions['fooTask'][1]['dateCreated'])->format('d.m.Y')
		);
		$this->assertEquals(
			'03.01.2016',
			DateTime::createFromFormat('U', $sortedTransactions['fooTask'][2]['dateCreated'])->format('d.m.Y')
		);
	}

}
