<?php

namespace Phragile\Domain;

// In Phabricator world when the task is merged into the other task (e.g. as it is considered a duplicate
// of the other task), the merged task is considered closed (there is no status change transaction
// changing status to "resolved").

class MergeAndCloseTransaction extends Transaction {

	const TYPE = 'mergeAndClose';

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
