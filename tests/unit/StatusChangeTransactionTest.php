<?php

use Phragile\StatusChangeTransaction;

/**
 * @covers Phragile\StatusChangeTransaction
 */
class StatusChangeTransactionTest extends PHPUnit_Framework_TestCase
{
	public function testConstructorsSetsFields()
	{
		$transaction = new StatusChangeTransaction([
			'timestamp' => '1451638800',
			'oldStatus' => 'open',
			'newStatus' => 'resolved',
		]);
		$this->assertEquals('open', $transaction->getOldStatus());
		$this->assertEquals('resolved', $transaction->getNewStatus());
		$this->assertEquals(
			'01.01.2016',
			DateTime::createFromFormat('U', $transaction->getTimestamp())->format('d.m.Y')
		);
	}

	public function testOldStatusCanBeNull()
	{
		$transaction = new StatusChangeTransaction([
			'timestamp' => '1451638800',
			'oldStatus' => null,
			'newStatus' => 'open',
		]);
		$this->assertNull($transaction->getOldStatus());
		$this->assertEquals('open', $transaction->getNewStatus());
		$this->assertEquals(
			'01.01.2016',
			DateTime::createFromFormat('U', $transaction->getTimestamp())->format('d.m.Y')
		);
	}

	public function incompleteAttributes()
	{
		$completeAttributes = [
			'timestamp' => '1451638800',
			'oldStatus' => 'open',
			'newStatus' => 'resolved',
		];
		$attributeCombinations = [];

		foreach (array_keys($completeAttributes) as $key)
		{
			$combination = $completeAttributes;
			unset($combination[$key]);
			$attributeCombinations[] = [$key, $combination];
		}

		return $attributeCombinations;
	}

	/**
	 * @dataProvider incompleteAttributes
	 */
	public function testThrowsExceptionWithMissingAttributes($missingField, $incompleteAttributes)
	{
		$expectedExceptionMessage = 'The ' . $missingField  . ' field is missing';
		$this->setExpectedException(InvalidArgumentException::class, $expectedExceptionMessage);
		new StatusChangeTransaction($incompleteAttributes);
	}

	public function testGetTransactionData()
	{
		$transaction = new StatusChangeTransaction([
			'timestamp' => '1451638800',
			'oldStatus' => 'open',
			'newStatus' => 'resolved',
		]);
		$this->assertEquals(
			[
				'type' => 'statusChange',
				'timestamp' => '1451638800',
				'oldStatus' => 'open',
				'newStatus' => 'resolved',
			],
			$transaction->getData()
		);
	}

}
