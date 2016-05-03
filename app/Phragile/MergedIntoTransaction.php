<?php

namespace Phragile;

class MergedIntoTransaction extends Transaction {

	public function __construct($timestamp)
	{
		$this->timestamp = $timestamp;
	}

	public function getTransactionData()
	{
		return [
			'type' => 'mergedInto', // TODO: same thing as in ColumnChangeTransaction::getTransactionData
			'timestamp' => $this->timestamp,
		];
	}

}
