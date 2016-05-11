<?php

namespace Phragile;

class MergedIntoTransaction extends Transaction {

	const TYPE = 'mergedInto';

	public function __construct($timestamp)
	{
		$this->timestamp = $timestamp;
	}

	public function getTransactionData()
	{
		return [
			'type' => self::TYPE,
			'timestamp' => $this->timestamp,
		];
	}

}
