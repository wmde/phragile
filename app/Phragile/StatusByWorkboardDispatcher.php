<?php
namespace Phragile;

use Phragile\Domain\Task;

class StatusByWorkboardDispatcher implements StatusDispatcher {

	private $columns = null;
	private $sprint = null;

	/**
	 * @var array - Maps tasks to the columnPHIDs of their current workboard column
	 */
	private $taskColumnPHIDs = [];

	public function __construct(\Sprint $sprint, SortedTransactionList $transactions, ProjectColumnRepository $columns)
	{
		$this->sprint = $sprint;
		$this->taskColumnPHIDs = $this->extractColumnIDs($transactions->getTransactions());
		$this->columns = $columns;
	}

	public function getStatus(Task $task)
	{
		$phid = isset($this->taskColumnPHIDs[$task->getId()]) ? $this->taskColumnPHIDs[$task->getId()] : null;
		return $this->columns->getColumnName($phid) ?: $this->sprint->project->getDefaultColumn();
	}

	private function extractColumnIDs(array $transactions)
	{
		return array_map([$this, 'findCurrentColumn'], $transactions);
	}

	private function findCurrentColumn(array $taskTransactions)
	{
		return array_reduce($taskTransactions, function($column, Transaction $transaction)
		{
			return $transaction instanceof ColumnChangeTransaction
				&& $transaction->getWorkboardPHID() === $this->sprint->phid
				? $transaction->getNewColumnPHID()
				: $column;
		});
	}

	public function isClosed(Task $task)
	{
		return in_array(
			$this->getStatus($task),
			$this->sprint->project->getClosedColumns()
		);
	}
}
