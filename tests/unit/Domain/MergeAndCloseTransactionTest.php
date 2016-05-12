<?php

namespace Phragile\Tests\Domain;

use Phragile\Domain\MergeAndCloseTransaction;

/**
 * @covers Phragile\Domain\MergeAndCloseTransaction
 */
class MergeAndCloseTransactionTest extends \PHPUnit_Framework_TestCase {

	public function testConstructorSetsFields()
	{
		$transaction = new MergeAndCloseTransaction('1451638800');
		$this->assertEquals(
			'01.01.2016',
			\DateTime::createFromFormat('U', $transaction->getTimestamp())->format('d.m.Y')
		);
	}

	public function testGetTransactionData()
	{
		$transaction = new MergeAndCloseTransaction('1451638800');
		$this->assertEquals(
			[
				'type' => 'mergeAndClose',
				'timestamp' => '1451638800',
			],
			$transaction->getData()
		);
	}

}
