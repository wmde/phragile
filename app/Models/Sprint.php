<?php
use Phragile\TransactionLoader;
use Phragile\TransactionFilter;
use Phragile\PhabricatorAPI;

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
	 * Returns the sprint's snapshots without the snapshot data
	 *
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function sprintSnapshots()
	{
		return $this->hasMany('SprintSnapshot')
		            ->select(['id', 'sprint_id', 'created_at', 'total_points', 'task_count'])
		            ->orderBy('created_at', 'desc');
	}

	public function validate()
	{
		$rules = [
			'title' => 'required|unique:sprints,title,' . $this->id . ',id,project_id,' . $this->project_id,
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
		$phabricator = App::make('phabricator');
		$tasks = $this->fetchTasks($phabricator);
		$data = $this->fetchSnapshotData($phabricator, $tasks);
		$filteredTasks = $this->filterIgnoredColumns($tasks, $data['transactions'], $phabricator);

		return SprintSnapshot::create([
			'sprint_id' => $this->id,
			'data' => json_encode($data),
			'total_points' => $this->calculateTotalPoints($filteredTasks),
			'task_count' => count($filteredTasks),
		]);
	}

	private function calculateTotalPoints(array $tasks)
	{
		return array_reduce(
			$tasks,
			function($sum, $task)
			{
				return $sum + $task['auxiliary'][env('MANIPHEST_STORY_POINTS_FIELD')];
			},
			0
		);
	}

	private function fetchTasks(PhabricatorAPI $phabricator)
	{
		return $phabricator->queryTasksByProject($this->phid);
	}

	private function fetchSnapshotData(PhabricatorAPI $phabricator, array $tasks)
	{
		$taskIDs = array_map(function($task)
		{
			return $task['id'];
		}, $tasks);
		$transactions = (new TransactionLoader(
			new TransactionFilter(),
			$phabricator
		))->load($taskIDs);

		return [
			'tasks' => $tasks,
			'transactions' => $transactions,
		];
	}

	private function filterIgnoredColumns(array $tasks, array $transactions, PhabricatorAPI $phabricator)
	{
		$columnRepository = new \Phragile\ProjectColumnRepository(
			$this->phid,
			$transactions,
			$phabricator
		);
		$statuses = (new \Phragile\Factory\StatusDispatcherFactory(
			$this,
			$columnRepository,
			$transactions
		))->getStatusDispatcher();

		return array_filter($tasks, function($task) use($statuses) {
			return !in_array($statuses->getStatus($task), $this->project->getIgnoredColumns());
		});
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
