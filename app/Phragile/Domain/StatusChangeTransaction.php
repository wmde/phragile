<?php

namespace Phragile\Domain;

class StatusChangeTransaction extends Transaction {

	const TYPE = 'statusChange';

	/**
	 * @var string|null
	 */
	private $oldStatus;

	/**
	 * @var string
	 */
	private $newStatus;

	/**
	 * @param array $attributes associative array with keys:
	 *        - 'timestamp' string
	 *        - 'oldStatus' string|null null means no old status (initial setting of the status)
	 *        - 'newStatus' string
	 */
	public function __construct(array $attributes)
	{
		$fields = ['timestamp', 'oldStatus', 'newStatus'];
		foreach ($fields as $field)
		{
			if (!array_key_exists($field, $attributes))
			{
				throw new \InvalidArgumentException('The ' . $field . ' field is missing.');
			}
		}
		$this->timestamp = $attributes['timestamp'];
		$this->oldStatus = $attributes['oldStatus'];
		$this->newStatus = $attributes['newStatus'];
	}

	public function getOldStatus()
	{
		return $this->oldStatus;
	}

	public function getNewStatus()
	{
		return $this->newStatus;
	}

	public function getData()
	{
		return [
			'type' => self::TYPE,
			'timestamp' => $this->timestamp,
			'oldStatus' => $this->oldStatus,
			'newStatus' => $this->newStatus,
		];
	}
}
