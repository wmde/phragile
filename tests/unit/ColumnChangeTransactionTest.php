<?php

use Phragile\ColumnChangeTransaction;

/**
 * @covers Phragile\ColumnChangeTransaction
 */
class ColumnChangeTransactionTest extends PHPUnit_Framework_TestCase {

	public function testConstructorSetFields()
	{
		$transaction = new ColumnChangeTransaction([
			'timestamp' => '1451638800',
			'workboardPHID' => 'PHID-PROJ-123',
			'oldColumnPHID' => 'PHID-PCOL-123',
			'newColumnPHID' => 'PHID-PCOL-456',
		]);
		$this->assertEquals('PHID-PROJ-123', $transaction->getWorkboardPHID());
		$this->assertEquals('PHID-PCOL-123', $transaction->getOldColumnPHID());
		$this->assertEquals('PHID-PCOL-456', $transaction->getNewColumnPHID());
		$this->assertEquals(
			'01.01.2016',
			DateTime::createFromFormat('U', $transaction->getTimestamp())->format('d.m.Y')
		);
	}

	public function testConstructorNoOldColumn()
	{
		$transaction = new ColumnChangeTransaction([
			'timestamp' => '1451638800',
			'workboardPHID' => 'PHID-PROJ-123',
			'oldColumnPHID' => null,
			'newColumnPHID' => 'PHID-PCOL-123'
		]);
		$this->assertEquals('PHID-PROJ-123', $transaction->getWorkboardPHID());
		$this->assertNull($transaction->getOldColumnPHID());
		$this->assertEquals('PHID-PCOL-123', $transaction->getNewColumnPHID());
		$this->assertEquals(
			'01.01.2016',
			DateTime::createFromFormat('U', $transaction->getTimestamp())->format('d.m.Y')
		);
	}

	public function incompleteAttributes()
	{
		$completeAttributes = [
			'timestamp' => '1451638800',
			'workboardPHID' => 'PHID-PROJ-123',
			'oldColumnPHID' => 'PHID-PCOL-123',
			'newColumnPHID' => 'PHID-PCOL-456',
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
		$expectedExceptionMessage = 'The ' . $missingField  .' field is missing';
		$this->setExpectedException(InvalidArgumentException::class, $expectedExceptionMessage);
		new ColumnChangeTransaction($incompleteAttributes);
	}

	public function testGetTransactionData()
	{
		$transaction = new ColumnChangeTransaction([
			'timestamp' => '1451638800',
			'workboardPHID' => 'PHID-PROJ-123',
			'oldColumnPHID' => 'PHID-PCOL-123',
			'newColumnPHID' => 'PHID-PCOL-456',
		]);
		$this->assertEquals(
			[
				'type' => 'columnChange',
				'timestamp' => '1451638800',
				'workboardPHID' => 'PHID-PROJ-123',
				'oldColumnPHID' => 'PHID-PCOL-123',
				'newColumnPHID' => 'PHID-PCOL-456',
			],
			$transaction->getData()
		);
	}

}
