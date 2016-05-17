<?php

namespace App\Console\Commands\Lib;

use Phragile\Domain\Transaction;
use Phragile\TransactionRawDataProcessor;

class SnapshotTransactionDataConverter {

	public function convert(array $transactionData)
	{
		if (!$this->transactionDataIsInPhabricatorFormat($transactionData))
		{
			return $transactionData;
		}
		return $this->convertPhabricatorTransactionData($transactionData);
	}

	private function convertPhabricatorTransactionData(array $transactionData)
	{
		$processor = new TransactionRawDataProcessor();
		return array_map(
			function(array $taskTransactions)
			{
				return array_map(
					function(Transaction $transaction)
					{
						return $transaction->getData();
					},
					$taskTransactions
				);
			},
			$processor->process($transactionData)
		);
	}
	public function needsConversion(array $transactionData)
	{
		return $this->transactionDataIsInPhabricatorFormat($transactionData);
	}

	private function transactionDataIsInPhabricatorFormat(array $transactionData)
	{
		$taskId = array_keys($transactionData)[0];
		if (!is_array($transactionData[$taskId]))
		{
			return false;
		}
		$transaction = array_values($transactionData[$taskId])[0];
		return array_key_exists('taskID', $transaction) && array_key_exists('dateCreated', $transaction) &&
			array_key_exists('transactionType', $transaction) && array_key_exists('oldValue', $transaction) &&
			array_key_exists('newValue', $transaction);
	}

}
