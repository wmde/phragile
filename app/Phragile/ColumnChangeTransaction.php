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

	/**
	 * @param array $attributes associative array with keys:
	 *        - 'timestamp' string
	 *        - 'workboardPHID' string
	 *        - 'oldColumnPHID' string|false false means no old column (initial setting of the column)
	 *        - 'newColumnPHID' string
	 */
	public function __construct(array $attributes)
	{
		$fields = ['timestamp', 'workboardPHID', 'oldColumnPHID', 'newColumnPHID'];
		foreach ($fields as $field)
		{
			if (!array_key_exists($field, $attributes))
			{
				throw new \InvalidArgumentException('The ' . $field . ' field is missing.');
			}
		}
		$this->timestamp = $attributes['timestamp'];
		$this->workboardPHID = $attributes['workboardPHID'];
		$this->oldColumnPHID = $attributes['oldColumnPHID'];
		$this->newColumnPHID = $attributes['newColumnPHID'];
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
