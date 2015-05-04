<?php
namespace Phragile;

interface ClosedTimeDispatcher {
	public function isClosingTransaction(array $transaction);
}
