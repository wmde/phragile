<?php
namespace Phragile;

class Burndown {

	public function __construct(\Sprint $sprint, TaskList $tasks)
	{
		$this->sprint = $sprint;
		$this->tasks = $tasks;
	}

	public function days()
	{
		$days = [];

		for ($day = strtotime($this->sprint->sprint_start);
		     $day <= strtotime($this->sprint->sprint_end);
		     $day += 60*60*24)
		{
			$days[] = date('M j', $day);
		}

		return $days;
	}
}
