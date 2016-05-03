<?php

namespace Phragile;

class ColumnChangeTransaction extends Transaction {

	/**
	 * @var string
	 */
	private $workboardPHID;

	/**
	 * @var string
	 */
	private $oldColumnPHID;

	/**
	 * @var string
	 */
	private $newColumnPHID;

	// TODO: use array of values instead of n positional params?
	/**
	 * @param string $timestamp
	 * @param string $workboardPHID
	 * @param string|false $oldColumnPHID
	 * @param string $newColumnPHID
	 */
	public function __construct($timestamp, $workboardPHID, $oldColumnPHID, $newColumnPHID)
	{
		$this->timestamp = $timestamp;
		$this->workboardPHID = $workboardPHID;
		$this->oldColumnPHID = $oldColumnPHID;
		$this->newColumnPHID = $newColumnPHID;
	}

	public function getWorkboardPHID()
	{
		return $this->workboardPHID;
	}

	public function getOldColumnPHID()
	{
		return $this->oldColumnPHID;
	}

	public function getNewColumnPHID()
	{
		return $this->newColumnPHID;
	}

	public function getTransactionData()
	{
		return [
			'type' => 'columnChange', // TODO: this string should be moved to some constant,
			//                        //so it there is no need to type it in in other places. But constant of what? Transaction::sth?
			'timestamp' => $this->timestamp,
			'workboardPHID' => $this->workboardPHID,
			'oldColumnPHID' => $this->oldColumnPHID,
			'newColumnPHID' => $this->newColumnPHID,
		];
	}

}
