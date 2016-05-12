<?php

namespace Phragile;

use Phragile\Domain\Transaction;

class SortedTransactionList {
	/**
	 * @var Transaction[]
	 */
	private $transactions = [];

	/**
	 * @param Transaction[] $transactions
	 */
	public function __construct(array $transactions)
	{
		$this->transactions = $this->sortTaskTransactions($transactions);
	}

	public function getTransactions()
	{
		return $this->transactions;
	}

	private function sortTaskTransactions(array $transactions)
	{
		$sortedTransactions = [];
		foreach ($transactions as $taskID => $taskTransactions)
		{
			$sortedTransactions[$taskID] = $this->sortChronologically($taskTransactions);
		}
		return $sortedTransactions;
	}

	private function sortChronologically(array $transactions)
	{
		usort($transactions, function(Transaction $t1, Transaction $t2)
		{
			return $t1->getTimestamp() < $t2->getTimestamp() ? -1 : 1;
		});

		return $transactions;
	}
}
