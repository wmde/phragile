<?php
namespace Phragile;

class StatusByWorkboardDispatcher implements StatusDispatcher {
	private $transactions = [];
	private $columns = null;
	private $sprint = null;

	/**
	 * @var array - Maps tasks to the columnPHIDs of their current workboard column
	 */
	private $taskColumnPHIDs = [];

	public function __construct(\Sprint $sprint, TransactionList $transactions, ProjectColumnRepository $columns)
	{
		$this->sprint = $sprint;
		$this->transactions = $transactions->getChronologicallySorted();
		$this->taskColumnPHIDs = $this->extractColumnIDs($this->transactions);
		$this->columns = $columns;
	}

	public function getStatus(array $task)
	{
		$phid = isset($this->taskColumnPHIDs[$task['id']]) ? $this->taskColumnPHIDs[$task['id']] : null;
		return $this->columns->getColumnName($phid) ?: $this->sprint->project->getDefaultColumn();
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
				&& $transaction['oldValue']['projectPHID'] === $this->sprint->phid
				? $transaction['newValue']['columnPHIDs'][0]
				: $column;
		});
	}

	public function isClosed(array $task)
	{
		return in_array(
			$this->getStatus($task),
			$this->sprint->project->getClosedColumns()
		);
	}
}
