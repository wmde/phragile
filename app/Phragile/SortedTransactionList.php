<?php

namespace Phragile;

class SortedTransactionList {
	private $transactions = [];

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
		usort($transactions, function($t1, $t2)
		{
			return ($t1['dateCreated'] < $t2['dateCreated']) ? -1 : 1;
		});

		return $transactions;
	}
}
