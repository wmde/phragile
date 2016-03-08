<?php
namespace Phragile;

class Task {
	private $title;
	private $priority;
	private $points;
	private $status;
	private $closed;
	private $id;
	private $assigneePHID;
	private $assigneeName;
	private $cssClass;

	/**
	 * @param array $attributes - containing 'title', 'priority', 'points', 'status', 'closed', 'id', 'assigneePHID'
	 */
	public function __construct(array $attributes)
	{
		$fields = ['title', 'priority', 'points', 'status', 'closed', 'id', 'assigneePHID'];
		foreach ($fields as $field)
		{
			if (!array_key_exists($field, $attributes))
			{
				throw new \InvalidArgumentException("The $field field is missing.");
			}

			$this->$field = $attributes[$field];
		}
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @return string
	 */
	public function getPriority()
	{
		return $this->priority;
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
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getAssigneePHID()
	{
		return $this->assigneePHID;
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
