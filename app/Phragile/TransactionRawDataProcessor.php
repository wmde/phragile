<?php

namespace Phragile;

class TransactionRawDataProcessor {

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
				$transactions[] = new ColumnChangeTransaction(
					$this->getTransactionTimestamp($singleTransactionData),
					$this->getWorkboardPHID($singleTransactionData),
					$this->getOldColumnPHID($singleTransactionData),
					$this->getNewColumnPHID($singleTransactionData)
				);
			} elseif ($this->isStatusChangeTransaction($singleTransactionData))
			{
				$transactions[] = new StatusChangeTransaction(
					$this->getTransactionTimestamp($singleTransactionData),
					$this->getOldStatus($singleTransactionData),
					$this->getNewStatus($singleTransactionData)
				);
			} elseif ($this->isMergedIntoTransaction($singleTransactionData))
			{
				$transactions[] = new MergedIntoTransaction(
					$this->getTransactionTimestamp($singleTransactionData)
				);
			}
		}
		return $transactions;
	}

	private function isColumnChangeTransaction(array $rawData)
	{
		return array_key_exists('transactionType', $rawData)
			&& $rawData['transactionType'] === 'core:columns';
	}

	private function isStatusChangeTransaction(array $rawData)
	{
		return array_key_exists('transactionType', $rawData)
			&& $rawData['transactionType'] === 'status';
	}

	private function isMergedIntoTransaction(array $rawData)
	{
		return array_key_exists('transactionType', $rawData)
		&& $rawData['transactionType'] === 'mergedinto';
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
