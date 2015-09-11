<?php
namespace Phragile;

class TransactionFilter {
	/**
	 * Filters out irrelevant transactions
	 *
	 * @param array $transactions
	 * @return array $transactions
	 */
	public function filter(array $transactions)
	{
		return array_map(function($taskTransactions)
		{
			return array_filter($taskTransactions, [$this, 'isRelevantTransaction']);
		}, $transactions);
	}

	protected function isRelevantTransaction(array $transaction)
	{
		return $this->isWorkboardTransaction($transaction) || $this->isStatusTransaction($transaction);
	}

	protected function isWorkboardTransaction(array $transaction)
	{
		return $transaction['transactionType'] === 'projectcolumn';
	}

	protected function isStatusTransaction(array $transaction)
	{
		return $transaction['transactionType'] === 'status'
		    || $transaction['transactionType'] === 'mergedinto';
	}
}
