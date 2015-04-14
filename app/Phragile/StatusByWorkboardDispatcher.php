<?php
namespace Phragile;

class StatusByWorkboardDispatcher implements StatusDispatcher {
	private $transactions = [];
	private $columns = null;

	/**
	 * @var array - Maps tasks to the columnPHIDs of their current workboard column
	 */
	private $taskColumnPHIDs = [];

	public function __construct(array $transactions, ProjectColumnRepository $columns)
	{
		$this->transactions = $transactions;
		$this->taskColumnPHIDs = $this->extractColumnIDs($transactions);
		$this->columns = $columns;
	}

	public function getStatus(array $task)
	{
		return $this->columns->getColumnName($this->taskColumnPHIDs[$task['id']]);
	}

	private function extractColumnIDs(array $transactions)
	{
		return array_map([$this, 'findCurrentColumn'], $transactions);
	}

	private function findCurrentColumn(array $taskTransactions)
	{
		return array_reduce($taskTransactions, function($column, $transaction)
		{
			return $transaction['transactionType'] === 'projectcolumn'
				? $transaction['newValue']['columnPHIDs'][0]
				: $column;
		});
	}
}
