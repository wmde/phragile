<?php
namespace Phragile;

class ClosedTimeByStatusFieldDispatcher implements ClosedTimeDispatcher {
	// TODO: ideally these should come from a cached call to maniphest.querystatuses
	private static $STATUS_OPEN = ['stalled', 'open'];

	public function isClosingTransaction(Transaction $transaction)
	{
		if ($transaction instanceof StatusChangeTransaction)
		{
			return in_array($transaction->getOldStatus(), self::$STATUS_OPEN) &&
				!in_array($transaction->getNewStatus(), self::$STATUS_OPEN);
		}

		return $transaction instanceof MergeAndCloseTransaction;
	}
}
