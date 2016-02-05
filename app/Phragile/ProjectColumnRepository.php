<?php
namespace Phragile;

class ProjectColumnRepository {
	/**
	 * @var array|null maps workboard column PHIDs to a map of column data
	 */
	private $projectColumns = null;
	private $transactions;
	private $phabricator;
	private $projectPHID;

	public function __construct($projectPHID, array $transactions, PhabricatorAPI $phabricator)
	{
		$this->projectPHID = $projectPHID;
		$this->transactions = $transactions;
		$this->phabricator = $phabricator;
	}

	public function getColumnName($phid)
	{
		$this->initialize();
		return $phid ? $this->projectColumns[$phid]['name'] : null;
	}

	public function getColumnPHID($name)
	{
		$this->initialize();
		foreach ($this->projectColumns as $phid => $column)
		{
			if ($column['name'] === $name) return $phid;
		}
	}

	private function initialize()
	{
		if ($this->projectColumns === null)
		{
			$this->projectColumns = $this->fetchColumnData($this->transactions);
		}
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
			if ($transaction['transactionType'] === 'projectcolumn' && $transaction['oldValue']['projectPHID'] === $this->projectPHID)
			{
				$columns[] = $transaction['newValue']['columnPHIDs'][0];
				$columns[] = reset($transaction['oldValue']['columnPHIDs']);
			}

			return $columns;
		}, $columns);
	}
}
