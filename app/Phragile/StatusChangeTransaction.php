<?php

namespace Phragile;

class StatusChangeTransaction extends Transaction {

	/**
	 * @var string|null
	 */
	private $oldStatus;

	/**
	 * @var string TODO: or could this be also null?
	 */
	private $newStatus;

	// TODO: consider switching to array of values instead of 2 (or more) params
	// $trans = new StatusChangeTransaction(null, 'doing') is not really nice
	public function __construct($timestamp, $oldStatus, $newStatus)
	{
		$this->timestamp = $timestamp;
		$this->oldStatus = $oldStatus;
		$this->newStatus = $newStatus;
	}

	public function getOldStatus()
	{
		return $this->oldStatus;
	}

	public function getNewStatus()
	{
		return $this->newStatus;
	}

	public function getTransactionData()
	{
		return [
			'type' => 'statusChange', // TODO: same thing as in ColumnChangeTransaction::getTransactionData
			'timestamp' => $this->timestamp,
			'oldStatus' => $this->oldStatus,
			'newStatus' => $this->newStatus,
		];
	}
}
