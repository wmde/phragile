<?php
namespace Phragile\Presentation;

use Phragile\Domain\Task as DomainTask;

class Task {

	const CLOSED_TASK = true;
	const OPEN_TASK = false;

	private $domainTask;
	private $points;
	private $status;
	private $closed;
	private $assigneeName;
	private $cssClass;

	/**
	 * Task constructor.
	 * @param DomainTask $domainTask
	 * @param string $status
	 * @param bool $closed self::CLOSED_TASK or self::OPEN_TASK
	 * @param int $points
	 */
	public function __construct(DomainTask $domainTask, $status, $closed, $points)
	{
		$this->domainTask = $domainTask;
		$this->status = $status;
		$this->closed = $closed;
		$this->points = $points;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->domainTask->getTitle();
	}

	/**
	 * @return string
	 */
	public function getPriority()
	{
		return $this->domainTask->getPriority();
	}

	/**
	 * @return int
	 */
	public function getPoints()
	{
		return $this->points;
	}

	/**
	 * @return string
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @return boolean
	 */
	public function isClosed()
	{
		return $this->closed;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->domainTask->getId();
	}

	/**
	 * @return string
	 */
	public function getAssigneePHID()
	{
		return $this->domainTask->getAssigneePHID();
	}

	/**
	 * @param string $name
	 */
	public function setAssigneeName($name)
	{
		$this->assigneeName = $name;
	}

	/**
	 * @return string
	 */
	public function getAssigneeName()
	{
		return $this->assigneeName;
	}

	/**
	 * @return string
	 */
	public function getCssClass()
	{
		return $this->cssClass;
	}

	/**
	 * @param string $cssClass
	 */
	public function setCssClass($cssClass)
	{
		$this->cssClass = $cssClass;
	}
}
