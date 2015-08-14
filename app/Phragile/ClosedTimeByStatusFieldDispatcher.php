<?php
namespace Phragile;

class ClosedTimeByStatusFieldDispatcher implements ClosedTimeDispatcher {
	// TODO: ideally these should come from a cached call to maniphest.querystatuses
	private static $STATUS_OPEN = ['stalled', 'open'];

	public function isClosingTransaction(array $transaction)
	{
		if ($transaction['transactionType'] === 'status') {
			return in_array($transaction['oldValue'], self::$STATUS_OPEN) &&
			       !in_array($transaction['newValue'], self::$STATUS_OPEN);
		}

		return $transaction['transactionType'] === 'mergedinto';
	}
}
