<?php
namespace Phragile;

interface ClosedTimeDispatcher {
	public function isClosingTransaction(Transaction $transaction);
}
