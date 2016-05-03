<?php

namespace Phragile;

abstract class Transaction {

	// TODO: consider renaming the field to something more meaningful
	protected $timestamp;

	public function getTimestamp()
	{
		return $this->timestamp;
	}

	abstract public function getTransactionData();

}
