<?php
namespace Phragile;

class TransactionLoader {
	private $transactionFilter;
	private $phabricatorAPI;

	/**
	 * @param TransactionFilter $transactionFilter
	 * @param PhabricatorAPI $phabricatorAPI
	 */
	public function __construct(TransactionFilter $transactionFilter, PhabricatorAPI $phabricatorAPI)
	{
		$this->transactionFilter = $transactionFilter;
		$this->phabricatorAPI = $phabricatorAPI;
	}

	/**
	 * Loads task transactions from Phabricator in batches of 200
	 *
	 * @param $taskIDs
	 * @return array
	 */
	public function load($taskIDs)
	{
		$transactions = [];

		foreach (array_chunk($taskIDs, 200) as $batch)
		{
			// Not using array_merge here because it would remove the keys
			$transactions += $this->transactionFilter->filter($this->phabricatorAPI->getTaskTransactions($batch));
		}

		return $transactions;
	}
}
