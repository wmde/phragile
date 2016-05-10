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
				new ColumnChangeTransaction([
					'timestamp' => DateTime::createFromFormat('d.m.Y', '02.01.2016')->format('U'),
					'workboardPHID' => 'PHID-PROJ-AWESOME',
					'oldColumnPHID' => 'PHID-doing',
					'newColumnPHID' => 'PHID-done',
				]),
				new ColumnChangeTransaction([
					'timestamp' => DateTime::createFromFormat('d.m.Y', '01.01.2016')->format('U'),
					'workboardPHID' => 'PHID-PROJ-AWESOME',
					'oldColumnPHID' => 'PHID-backlog',
					'newColumnPHID' => 'PHID-doing',
				]),
				new ColumnChangeTransaction([
					'timestamp' => DateTime::createFromFormat('d.m.Y', '03.01.2016')->format('U'),
					'workboardPHID' => 'PHID-PROJ-AWESOME',
					'oldColumnPHID' => 'PHID-doing',
					'newColumnPHID' => 'PHID-done',
				]),
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
