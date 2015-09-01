<?php
use Phragile\TransactionLoader;
use Phragile\TransactionFilter;

class Sprint extends Eloquent {

	protected $fillable = ['phid', 'phabricator_id', 'project_id', 'title', 'sprint_start', 'sprint_end', 'ignore_estimates'];

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
		return $this->hasMany('SprintSnapshot')
		            ->select(['id', 'sprint_id', 'created_at', 'total_points'])
		            ->orderBy('created_at', 'desc');
	}

	public function validate()
	{
		$rules = [
			'title' => 'required|unique:sprints,title,NULL,id,project_id,' . $this->project_id,
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
		$this->title = $project['name'];
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
		$tasks = $this->fetchTasks();

		return SprintSnapshot::create([
			'sprint_id' => $this->id,
			'data' => json_encode($this->fetchSnapshotData($tasks)),
			'total_points' => $this->calculateTotalPoints($tasks),
		]);
	}

	private function calculateTotalPoints(array $tasks)
	{
		return array_reduce(
			$tasks,
			function($sum, $task)
			{
				return $sum + ($this->ignore_estimates ? 1 : $task['auxiliary'][env('MANIPHEST_STORY_POINTS_FIELD')]);
			},
			0
		);
	}

	private function fetchTasks()
	{
		return App::make('phabricator')->queryTasksByProject($this->phid);
	}

	private function fetchSnapshotData(array $tasks)
	{
		$taskIDs = array_map(function($task)
		{
			return $task['id'];
		}, $tasks);
		$transactionLoader = new TransactionLoader(new TransactionFilter());

		return [
			'tasks' => $tasks,
			'transactions' => $transactionLoader->load($taskIDs, App::make('phabricator')),
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
