<?php
namespace Phragile;

class SettingsAwareTransactionFilter extends TransactionFilter {
	private $workboardMode;

	public function __construct($workboardMode)
	{
		$this->workboardMode = $workboardMode;
	}

	protected function isRelevantTransaction(array $transaction)
	{
		return ($this->workboardMode && $this->isWorkboardTransaction($transaction))
			|| (!$this->workboardMode && $this->isStatusTransaction($transaction));
	}
}
