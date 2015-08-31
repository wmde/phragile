<?php
namespace Phragile;

class TransactionLoader {
	private $transactionFilter;

	public function __construct(TransactionFilter $transactionFilter)
	{
		$this->transactionFilter = $transactionFilter;
	}

	public function load($taskIDs, PhabricatorAPI $phabricatorAPI)
	{
		$transactions = [];

		foreach (array_chunk($taskIDs, 200) as $batch)
		{
			// Not using array_merge here because it would remove the keys
			$transactions += $this->transactionFilter->filter($phabricatorAPI->getTaskTransactions($batch));
		}

		return $transactions;
	}
}
