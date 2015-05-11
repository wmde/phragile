<?php

class Sprint extends Eloquent {

	protected $fillable = ['phid', 'phabricator_id', 'project_id', 'title', 'sprint_start', 'sprint_end'];

	private $phabricatorError = null;
	private $days = null;

	/**
	 * @return Project
	 */
	public function project()
	{
		return $this->belongsTo('Project');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function sprintSnapshots()
	{
		return $this->hasMany('SprintSnapshot')->orderBy('created_at', 'desc');
	}

	public function validate()
	{
		$rules = [
			'title' => 'required',
			'sprint_start' => 'required|date_format:"Y-m-d"',
			'sprint_end' => 'required|date_format:"Y-m-d"|after:' . ($this->sprint_start ?: '0-0-0')
		];

		return Validator::make(
			$this->getAttributes(),
			$rules
		);
	}

	public function getPhabricatorError()
	{
		return $this->phabricatorError;
	}

	/**
	 * Returns a formatted list of dates from sprint_start to sprint_end
	 *
	 * @param string $format
	 * @return array
	 */
	public function getFormattedDays($format = 'M j')
	{
		return array_map(function($date) use($format)
		{
			return date($format, $date);
		}, $this->getDays());
	}

	private function getDays()
	{
		if ($this->days === null) $this->days = $this->computeDays();

		return $this->days;
	}

	public function connectWithPhabricatorProject(array $project)
	{
		$this->phid = $project['phid'];
		$this->phabricator_id = $project['id'];
	}

	private function computeDays()
	{
		$days = [];

		for ($day = strtotime($this->sprint_start);
			 $day <= strtotime($this->sprint_end);
			 $day += 60*60*24)
		{
			$days[] = $day;
		}

		return $days;
	}

	/**
	 * @return SprintSnapshot
	 */
	public function createSnapshot()
	{
		return SprintSnapshot::create([
			'sprint_id' => $this->id,
			'data' => json_encode($this->fetchSnapshotData())
		]);
	}

	private function fetchSnapshotData()
	{
		$phabricator = App::make('phabricator');
		$tasks = $phabricator->queryTasksByProject($this->phid);
		$taskIDs = array_map(function($task)
		{
			return $task['id'];
		}, $tasks);

		return [
			'tasks' => $tasks,
			'transactions' => $phabricator->getTaskTransactions($taskIDs),
		];
	}

	/**
	 * @return bool
	 */
	public function hasEnded()
	{
		return $this->sprint_end < date('Y-m-d');
	}

	/**
	 * @return bool
	 */
	public function isActive()
	{
		return $this->sprint_start <= date('Y-m-d') && !$this->hasEnded();
	}
}
