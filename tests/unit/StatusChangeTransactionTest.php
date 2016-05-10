<?php

use Phragile\StatusChangeTransaction;

/**
 * @covers Phragile\StatusChangeTransaction
 */
class StatusChangeTransactionTest extends PHPUnit_Framework_TestCase
{
	public function testConstructorsSetsFields()
	{
		$transaction = new StatusChangeTransaction('1451638800', 'open', 'resolved');
		$this->assertEquals('open', $transaction->getOldStatus());
		$this->assertEquals('resolved', $transaction->getNewStatus());
		$this->assertEquals(
			'01.01.2016',
			DateTime::createFromFormat('U', $transaction->getTimestamp())->format('d.m.Y')
		);
	}

	public function testOldStatusCanBeNull()
	{
		$transaction = new StatusChangeTransaction('1451638800', null, 'open');
		$this->assertNull($transaction->getOldStatus());
		$this->assertEquals('open', $transaction->getNewStatus());
		$this->assertEquals(
			'01.01.2016',
			DateTime::createFromFormat('U', $transaction->getTimestamp())->format('d.m.Y')
		);
	}

	public function testGetTransactionData()
	{
		$transaction = new StatusChangeTransaction('1451638800', 'open', 'resolved');
		$this->assertEquals(
			[
				'type' => 'statusChange',
				'timestamp' => '1451638800',
				'oldStatus' => 'open',
				'newStatus' => 'resolved',
			],
			$transaction->getTransactionData()
		);
	}

}
