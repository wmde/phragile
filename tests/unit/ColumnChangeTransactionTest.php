<?php

use Phragile\ColumnChangeTransaction;

/**
 * @covers Phragile\ColumnChangeTransaction
 */
class ColumnChangeTransactionTest extends PHPUnit_Framework_TestCase {

	public function testConstructorSetFields()
	{
		$transaction = new ColumnChangeTransaction('1451638800', 'PHID-PROJ-123', 'PHID-PCOL-123', 'PHID-PCOL-456');
		$this->assertEquals('PHID-PROJ-123', $transaction->getWorkboardPHID());
		$this->assertEquals('PHID-PCOL-123', $transaction->getOldColumnPHID());
		$this->assertEquals('PHID-PCOL-456', $transaction->getNewColumnPHID());
		$this->assertEquals(
			'01.01.2016',
			DateTime::createFromFormat('U', $transaction->getTimestamp())->format('d.m.Y')
		);

	}

	// TODO: tests for no old column case!

	public function testGetTransactionData()
	{
		$transaction = new ColumnChangeTransaction('1451638800', 'PHID-PROJ-123', 'PHID-PCOL-123', 'PHID-PCOL-456');
		$this->assertEquals(
			[
				'type' => 'columnChange',
				'timestamp' => '1451638800',
				'workboardPHID' => 'PHID-PROJ-123',
				'oldColumnPHID' => 'PHID-PCOL-123',
				'newColumnPHID' => 'PHID-PCOL-456',
			],
			$transaction->getTransactionData()
		);
	}

}
