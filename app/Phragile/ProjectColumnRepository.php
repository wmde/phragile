<?php
namespace Phragile;

class ProjectColumnRepository {
	/**
	 * @var array|null maps workboard column PHIDs to a map of column data
	 */
	private $projectColumns = null;
	/**
	 * @var Transaction[]
	 */
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
			if ($column['name'] === $name)
			{
				return $phid;
			}
		}
		return null;
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
		return array_reduce($transactions, function($columns, Transaction $transaction)
		{
			if ($transaction instanceof ColumnChangeTransaction && $transaction->getWorkboardPHID() === $this->projectPHID)
			{
				$columns[] = $transaction->getNewColumnPHID();
				$columns[] = $transaction->getOldColumnPHID();
			}

			return $columns;
		}, $columns);
	}
}
