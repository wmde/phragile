<?php
namespace Phragile;

use Phragile\Domain\Transaction;

interface ClosedTimeDispatcher {
	public function isClosingTransaction(Transaction $transaction);
}
