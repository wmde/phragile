<?php

use Phragile\ColumnChangeTransaction;
use Phragile\SortedTransactionList;

/**
 * @covers Phragile\SortedTransactionList
 */
class SortedTransactionListTest extends PHPUnit_Framework_TestCase {

	public function testTaskTransactionsAreSortedByTimestamp()
	{
		$transactions = [
			'fooTask' => [
				new ColumnChangeTransaction(
					DateTime::createFromFormat('d.m.Y', '02.01.2016')->format('U'),
					'PHID-PROJ-AWESOME',
					'PHID-doing',
					'PHID-done'
				),
				new ColumnChangeTransaction(
					DateTime::createFromFormat('d.m.Y', '01.01.2016')->format('U'),
					'PHID-PROJ-AWESOME',
					'PHID-backlog',
					'PHID-doing'
				),
				new ColumnChangeTransaction(
					DateTime::createFromFormat('d.m.Y', '03.01.2016')->format('U'),
					'PHID-PROJ-AWESOME',
					'PHID-doing',
					'PHID-done'
				),
			]
		];
		$sortedTransactions = (new SortedTransactionList($transactions))->getTransactions();
		$this->assertEquals(
			'01.01.2016',
			DateTime::createFromFormat('U', $sortedTransactions['fooTask'][0]->getTimestamp())->format('d.m.Y')
		);
		$this->assertEquals(
			'02.01.2016',
			DateTime::createFromFormat('U', $sortedTransactions['fooTask'][1]->getTimestamp())->format('d.m.Y')
		);
		$this->assertEquals(
			'03.01.2016',
			DateTime::createFromFormat('U', $sortedTransactions['fooTask'][2]->getTimestamp())->format('d.m.Y')
		);
	}

}
