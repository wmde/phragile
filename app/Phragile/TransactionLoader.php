<?php
namespace Phragile;

class TransactionLoader {

	public function load($taskIDs, PhabricatorAPI $phabricatorAPI)
	{
		$transactions = [];

		foreach (array_chunk($taskIDs, 200) as $batch)
		{
			// Not using array_merge here because it would remove the keys
			$transactions += $this->removeIrrelevant($phabricatorAPI->getTaskTransactions($batch));
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
		return $this->isWorkboardTransaction($transaction) || $this->isStatusTransaction($transaction);
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
