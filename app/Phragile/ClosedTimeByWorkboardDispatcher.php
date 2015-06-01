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
		return $transaction['transactionType'] === 'projectcolumn'
			&& $transaction['oldValue']['projectPHID'] === $this->phid
			&& in_array($transaction['newValue']['columnPHIDs'][0], $this->closedColumnPHIDs)
			&& (empty($transaction['oldValue']['columnPHIDs'])
				|| !in_array(reset($transaction['oldValue']['columnPHIDs']), $this->closedColumnPHIDs));
	}
}
