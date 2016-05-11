<?php

namespace Phragile;

class MergedIntoTransaction extends Transaction {

	const TYPE = 'mergedInto';

	public function __construct($timestamp)
	{
		$this->timestamp = $timestamp;
	}

	public function getData()
	{
		return [
			'type' => self::TYPE,
			'timestamp' => $this->timestamp,
		];
	}

}
