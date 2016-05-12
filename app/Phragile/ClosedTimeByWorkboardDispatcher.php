<?php
namespace Phragile;

use Phragile\Domain\ColumnChangeTransaction;
use Phragile\Domain\Transaction;

class ClosedTimeByWorkboardDispatcher implements ClosedTimeDispatcher {
	private $phid = null;
	private $closedColumnPHIDs = [];

	public function __construct($phid, array $closedColumnPHIDs)
	{
		$this->phid = $phid;
		$this->closedColumnPHIDs = $closedColumnPHIDs;
	}

	public function isClosingTransaction(Transaction $transaction)
	{
		return $transaction instanceof ColumnChangeTransaction
			&& $transaction->getWorkboardPHID() === $this->phid
			&& in_array($transaction->getNewColumnPHID(), $this->closedColumnPHIDs)
			&& ($transaction->getOldColumnPHID() === false
				|| !in_array($transaction->getOldColumnPHID(), $this->closedColumnPHIDs));
	}
}
