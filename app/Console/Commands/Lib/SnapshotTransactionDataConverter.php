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
		if ($this->transactionDataIsInPre2016Week15Format($transactionData))
		{
			$transactionData = $this->convertPre2016Week15Transactions($transactionData);
		}
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
		if (!is_array($transactionData[$taskId]) || empty($transactionData[$taskId]))
		{
			return false;
		}
		$transaction = array_values($transactionData[$taskId])[0];
		return array_key_exists('taskID', $transaction) && array_key_exists('dateCreated', $transaction) &&
			array_key_exists('transactionType', $transaction) && array_key_exists('oldValue', $transaction) &&
			array_key_exists('newValue', $transaction);
	}

	private function transactionDataIsInPre2016Week15Format(array $transactionData)
	{
		foreach ($transactionData as $taskId => $taskTransactions)
		{
			$pre2016Week15ColumnTransactions = array_filter(
				$taskTransactions,
				function(array $transaction)
				{
					return $transaction['transactionType'] === 'projectcolumn' &&
						array_key_exists('columnPHIDs', $transaction['oldValue']) &&
						array_key_exists('projectPHID', $transaction['oldValue']) &&
						array_key_exists('columnPHIDs', $transaction['newValue']) &&
						array_key_exists('projectPHID', $transaction['newValue']);
				}
			);
			if (!empty($pre2016Week15ColumnTransactions))
			{
				return true;
			}
		}
		return false;
	}

	private function convertPre2016Week15Transactions(array $transactionData)
	{
		return array_map(
			function(array $taskTransactions)
			{
				return array_map(
					function(array $transaction)
					{
						return $transaction['transactionType'] === 'projectcolumn'
							? $this->convertPre2016Week15ColumnChangeTransaction($transaction)
							: $transaction;
					},
					$taskTransactions
				);
			},
			$transactionData
		);
	}

	private function convertPre2016Week15ColumnChangeTransaction(array $transaction)
	{
		$projectPHID = $transaction['oldValue']['projectPHID'];
		$newColumnPHID = $transaction['newValue']['columnPHIDs'][0];
		$oldColumnPHID = reset($transaction['oldValue']['columnPHIDs']);
		$fromColumnsPart = $oldColumnPHID !== false ? [$oldColumnPHID => $oldColumnPHID] : [];
		$convertedTransactionData = [
			'transactionType' => 'core:columns',
			'oldValue' => null,
			'newValue' => [
				[
					'columnPHID' => $newColumnPHID,
					'boardPHID' => $projectPHID,
					'fromColumnPHIDs' => $fromColumnsPart,
				],
			],
		];
		return array_merge(
			$transaction,
			$convertedTransactionData
		);
	}

}
