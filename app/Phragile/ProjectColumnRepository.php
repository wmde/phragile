<?php
namespace Phragile;

class ProjectColumnRepository {
	private $projectColumns;
	private $phabricator;

	public function __construct(array $transactions, PhabricatorAPI $phabricator)
	{
		$this->phabricator = $phabricator;
		$this->projectColumns = $this->fetchColumnData($transactions);
	}

	public function getColumnName($phid)
	{
		return $phid ? $this->projectColumns[$phid]['name'] : null;
	}

	private function fetchColumnData(array $transactions)
	{
		return $this->phabricator->queryPHIDs(
			array_values($this->extractColumnPHIDs($transactions))
		);
	}

	private function extractColumnPHIDs(array $transactions)
	{
		return array_unique(array_reduce($transactions, [$this, 'findColumnPHIDs'], []));
	}

	private function findColumnPHIDs($columns, $transactions)
	{
		return array_reduce($transactions, function($columns, $transaction)
		{
			if ($transaction['transactionType'] === 'projectcolumn')
			{
				$columns[] = $transaction['newValue']['columnPHIDs'][0];
				$columns[] = reset($transaction['oldValue']['columnPHIDs']);
			}

			return $columns;
		}, $columns);
	}
}
