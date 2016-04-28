<?php
namespace Phragile;

class ClosedTimeByWorkboardDispatcher implements ClosedTimeDispatcher {
	private $phid = null;
	private $closedColumnPHIDs = [];

	public function __construct($phid, array $closedColumnPHIDs)
	{
		$this->phid = $phid;
		$this->closedColumnPHIDs = $closedColumnPHIDs;
	}

	public function isClosingTransaction(array $transaction)
	{
		return $transaction['transactionType'] === 'core:columns'
			&& $transaction['newValue'][0]['boardPHID'] === $this->phid
			&& in_array($transaction['newValue'][0]['columnPHID'], $this->closedColumnPHIDs)
			&& (empty($transaction['newValue'][0]['fromColumnPHIDs'])
				|| !in_array(reset($transaction['newValue'][0]['fromColumnPHIDs']), $this->closedColumnPHIDs));
	}
}
