<?php

use Phragile\MergedIntoTransaction;

/**
 * @covers Phragile\MergedIntoTransaction
 */
class MergedIntoTransactionTest extends PHPUnit_Framework_TestCase {

	public function testConstructorSetsFields()
	{
		$transaction = new MergedIntoTransaction('1451638800');
		$this->assertEquals(
			'01.01.2016',
			DateTime::createFromFormat('U', $transaction->getTimestamp())->format('d.m.Y')
		);
	}

	public function testGetTransactionData()
	{
		$transaction = new MergedIntoTransaction('1451638800');
		$this->assertEquals(
			[
				'type' => 'mergedInto',
				'timestamp' => '1451638800',
			],
			$transaction->getData()
		);
	}

}
