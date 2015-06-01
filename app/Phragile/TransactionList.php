<?php
namespace Phragile;

class TransactionList {
	private $transactions = [];

	public function __construct(array $transactions)
	{
		$this->transactions = $transactions;
	}

	public function getChronologicallySorted()
	{
		foreach ($this->transactions as $taskID => $taskTransactions)
		{
			$this->transactions[$taskID] = $this->sortChronologically($taskTransactions);
		}

		return $this->transactions;
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
