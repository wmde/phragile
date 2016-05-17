<?php

namespace Phragile;

use Phragile\Domain\ColumnChangeTransaction;
use Phragile\Domain\MergeAndCloseTransaction;
use Phragile\Domain\StatusChangeTransaction;
use Phragile\Domain\Transaction;

class TransactionSnapshotDataProcessor {

	public function process(array $snapshotData)
	{
		$transactions = [];
		foreach ($snapshotData as $taskID => $taskTransactions)
		{
			$transactions[$taskID] = array_filter(array_map([$this, 'getSnapshotTransaction'], $taskTransactions));
		}
		return $transactions;
	}

	/**
	 * @param array $snapshotTransaction
	 * @return Transaction|false
	 */
	private function getSnapshotTransaction(array $snapshotTransaction)
	{
		if (!array_key_exists('type', $snapshotTransaction))
		{
			return false;
		}
		if ($snapshotTransaction['type'] === ColumnChangeTransaction::TYPE)
		{
			return new ColumnChangeTransaction([
				'timestamp' => $snapshotTransaction['timestamp'],
				'workboardPHID' => $snapshotTransaction['workboardPHID'],
				'oldColumnPHID' => $snapshotTransaction['oldColumnPHID'],
				'newColumnPHID' => $snapshotTransaction['newColumnPHID'],
			]);
		} elseif ($snapshotTransaction['type'] === StatusChangeTransaction::TYPE)
		{
			return new StatusChangeTransaction([
				'timestamp' => $snapshotTransaction['timestamp'],
				'oldStatus' => $snapshotTransaction['oldStatus'],
				'newStatus' => $snapshotTransaction['newStatus']
			]);
		} elseif ($snapshotTransaction['type'] === MergeAndCloseTransaction::TYPE)
		{
			return new MergeAndCloseTransaction($snapshotTransaction['timestamp']);
		}
		return false;
	}

}
