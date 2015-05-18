<?php
namespace Phragile;

class StatusByWorkboardDispatcher implements StatusDispatcher {
	private $transactions = [];
	private $columns = null;

	/**
	 * @var array - Maps tasks to the columnPHIDs of their current workboard column
	 */
	private $taskColumnPHIDs = [];

	/**
	 * @var array - names of columns indicating that a task is closed
	 */
	private $closedColumnNames = [];

	public function __construct(array $transactions, ProjectColumnRepository $columns, array $closedColumnNames)
	{
		$this->transactions = $transactions;
		$this->taskColumnPHIDs = $this->extractColumnIDs($transactions);
		$this->columns = $columns;
		$this->closedColumnNames = $closedColumnNames;
	}

	public function getStatus(array $task)
	{
		return $this->columns->getColumnName($this->taskColumnPHIDs[$task['id']]) ?: 'Backlog';
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

	public function isClosed(array $task)
	{
		return in_array(
			$this->columns->getColumnName($this->taskColumnPHIDs[$task['id']]),
			$this->closedColumnNames
		);
	}
}
