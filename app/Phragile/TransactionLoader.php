<?php
namespace Phragile;

class TransactionLoader {
	private $workboardMode;

	public function __construct($workboardMode)
	{
		$this->workboardMode = $workboardMode;
	}

	public function load($taskIDs, PhabricatorAPI $phabricatorAPI)
	{
		$transactions = [];

		foreach (array_chunk($taskIDs, 200) as $batch)
		{
			$transactions = array_merge(
				$transactions,
				$this->removeIrrelevant($phabricatorAPI->getTaskTransactions($batch))
			);
		}

		return $transactions;
	}

	private function removeIrrelevant(array $transactions)
	{
		return array_map(function($taskTransactions)
		{
			return array_filter($taskTransactions, [$this, 'isRelevantTransaction']);
		}, $transactions);
	}

	private function isRelevantTransaction(array $transaction)
	{
		return ($this->workboardMode && $this->isWorkboardTransaction($transaction))
			|| (!$this->workboardMode && $this->isStatusTransaction($transaction));
	}

	private function isWorkboardTransaction(array $transaction)
	{
		return $transaction['transactionType'] === 'projectcolumn';
	}

	private function isStatusTransaction(array $transaction)
	{
		return $transaction['transactionType'] === 'status'
			|| $transaction['transactionType'] === 'mergedinto';
	}
}
