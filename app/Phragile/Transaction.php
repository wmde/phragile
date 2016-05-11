<?php

namespace Phragile;

abstract class Transaction {

	const TYPE = 'undefined';

	// TODO: consider renaming the field to something more meaningful
	protected $timestamp;

	public function getTimestamp()
	{
		return $this->timestamp;
	}

	// TODO: would getSnapshotData be a better name?
	abstract public function getTransactionData();

}
