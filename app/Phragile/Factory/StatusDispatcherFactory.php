<?php
namespace Phragile\Factory;

use Phragile\ProjectColumnRepository;
use Phragile\StatusDispatcher;
use Phragile\StatusByStatusFieldDispatcher;
use Phragile\StatusByWorkboardDispatcher;
use Phragile\TransactionList;

class StatusDispatcherFactory
{
	private $sprint = null;
	private $projectColumnRepository = null;
	private $transactions = [];

	public function __construct(\Sprint $sprint, ProjectColumnRepository $projectColumnRepository, array $transactions)
	{
		$this->sprint = $sprint;
		$this->projectColumnRepository = $projectColumnRepository;
		$this->transactions = $transactions;
	}

	/**
	 * @return StatusDispatcher
	 */
	public function getStatusDispatcher()
	{
		return $this->sprint->project->workboard_mode ? $this->getWorkboardDispatcher() : $this->getFieldDispatcher();
	}

	private function getWorkboardDispatcher()
	{
		return new StatusByWorkboardDispatcher(
			$this->sprint->phid,
			new TransactionList($this->transactions),
			$this->projectColumnRepository,
			$this->sprint->project->getClosedColumns()
		);
	}

	private function getFieldDispatcher()
	{
		return new StatusByStatusFieldDispatcher(env('REVIEW_TAG_PHID'));
	}
}
