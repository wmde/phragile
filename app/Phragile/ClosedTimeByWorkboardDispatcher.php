<?php
namespace Phragile;

class ClosedTimeByWorkboardDispatcher implements ClosedTimeDispatcher {
	private $closedColumnPHIDs = [];

	public function __construct(array $closedColumnPHIDs)
	{
		$this->closedColumnPHIDs = $closedColumnPHIDs;
	}

	public function isClosingTransaction(array $transaction)
	{
		return $transaction['transactionType'] === 'projectcolumn'
			&& in_array($transaction['newValue']['columnPHIDs'][0], $this->closedColumnPHIDs)
			&& (empty($transaction['oldValue']['columnPHIDs'])
				|| !in_array(reset($transaction['oldValue']['columnPHIDs']), $this->closedColumnPHIDs));
	}
}
