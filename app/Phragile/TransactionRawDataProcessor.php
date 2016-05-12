<?php

namespace Phragile;

class TransactionRawDataProcessor {

	const COLUMN_CHANGE_TRANSACTION = 'core:columns';
	const STATUS_CHANGE_TRANSACTION = 'status';
	const MERGE_AND_CLOSE_TRANSACTION = 'mergedinto';

	public function process(array $rawData)
	{
		$transactions = [];
		foreach ($rawData as $task => $taskTransactionData)
		{
			$transactions[$task] = $this->processTaskTransactionData($taskTransactionData);
		}
		return $transactions;
	}

	private function processTaskTransactionData(array $rawTaskTransactionData)
	{
		$transactions = [];
		foreach ($rawTaskTransactionData as $singleTransactionData)
		{
			if ($this->isColumnChangeTransaction($singleTransactionData))
			{
				$transactions[] = new ColumnChangeTransaction([
					'timestamp' => $this->getTransactionTimestamp($singleTransactionData),
					'workboardPHID' => $this->getWorkboardPHID($singleTransactionData),
					'oldColumnPHID' => $this->getOldColumnPHID($singleTransactionData),
					'newColumnPHID' => $this->getNewColumnPHID($singleTransactionData),
				]);
			} elseif ($this->isStatusChangeTransaction($singleTransactionData))
			{
				$transactions[] = new StatusChangeTransaction([
					'timestamp' => $this->getTransactionTimestamp($singleTransactionData),
					'oldStatus' => $this->getOldStatus($singleTransactionData),
					'newStatus' => $this->getNewStatus($singleTransactionData),
				]);
			} elseif ($this->isMergeAndCloseTransaction($singleTransactionData))
			{
				$transactions[] = new MergeAndCloseTransaction(
					$this->getTransactionTimestamp($singleTransactionData)
				);
			}
		}
		return $transactions;
	}

	private function isColumnChangeTransaction(array $rawData)
	{
		return array_key_exists('transactionType', $rawData)
			&& $rawData['transactionType'] === self::COLUMN_CHANGE_TRANSACTION;
	}

	private function isStatusChangeTransaction(array $rawData)
	{
		return array_key_exists('transactionType', $rawData)
			&& $rawData['transactionType'] === self::STATUS_CHANGE_TRANSACTION;
	}

	private function isMergeAndCloseTransaction(array $rawData)
	{
		return array_key_exists('transactionType', $rawData)
		&& $rawData['transactionType'] === self::MERGE_AND_CLOSE_TRANSACTION;
	}

	private function getTransactionTimestamp(array $rawData)
	{
		return $rawData['dateCreated'];
	}

	private function getWorkboardPHID(array $rawData)
	{
		return $rawData['newValue'][0]['boardPHID'];
	}

	private function getOldColumnPHID(array $rawData)
	{
		return reset($rawData['newValue'][0]['fromColumnPHIDs']);
	}

	private function getNewColumnPHID(array $rawData)
	{
		return $rawData['newValue'][0]['columnPHID'];
	}

	private function getOldStatus(array $rawData)
	{
		return $rawData['oldValue'];
	}

	private function getNewStatus(array $rawData)
	{
		return $rawData['newValue'];
	}

}
