<?php

namespace Phragile\Domain;

class Task {

	private $id;
	// TODO: what about task's PHID? Any foreseeable usage for it?
	private $title;
	private $priority;
	private $points;
	private $status;
	private $projectPHIDs = [];
	private $assigneePHID;
	private $customFields = [];

	/**
	 * @param array $attributes - containing 'id', 'title', 'priority', 'status', 'points', 'projectPHIDs', 'assigneePHID'
	 */
	public function __construct(array $attributes)
	{
		$fields = ['id', 'title', 'priority', 'status', 'points', 'projectPHIDs', 'assigneePHID'];
		foreach ($fields as $field)
		{
			if (!array_key_exists($field, $attributes))
			{
				throw new \InvalidArgumentException('The ' . $field . ' field is missing.');
			}

			$this->$field = $attributes[$field];
		}
		if (array_key_exists('customFields', $attributes))
		{
			if (!is_array($attributes['customFields']))
			{
				throw new \InvalidArgumentException('The customFields field must be an array.');
			}
			$this->customFields = $attributes['customFields'];
		}
	}

	public function getId()
	{
		return $this->id;
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
	 * @return int|null
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
	 * @return string[]
	 */
	public function getProjectPHIDs()
	{
		return $this->projectPHIDs;
	}

	/**
	 * @return string
	 */
	public function getAssigneePHID()
	{
		return $this->assigneePHID;
	}

	/**
	 * @return array
	 */
	public function getCustomFields()
	{
		return $this->customFields;
	}

	public function getData()
	{
		return [
			'id' => $this->id,
			'title' => $this->title,
			'priority' => $this->priority,
			'status' => $this->status,
			'points' => $this->points,
			'projectPHIDs' => $this->projectPHIDs,
			'assigneePHID' => $this->assigneePHID,
			'customFields' => $this->customFields
		];
	}

}
