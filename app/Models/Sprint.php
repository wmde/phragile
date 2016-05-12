<?php
use Phragile\Factory\StatusDispatcherFactory;
use Phragile\ProjectColumnRepository;
use Phragile\Domain\Task;
use Phragile\TaskDataFetcher;
use Phragile\TaskRawDataProcessor;
use Phragile\TaskPresenter;
use Phragile\TaskList;
use Phragile\Domain\Transaction;
use Phragile\TransactionRawDataProcessor;
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
		if ($this->days === null)
		{
			$this->days = $this->computeDays();
		}

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

		$startTime = strtotime($this->sprint_start);
		$endTime = strtotime($this->sprint_end);
		for ($day = $startTime;
			 $day <= $endTime;
			 $day += 60 * 60 * 24)
		{
			$days[] = $day;
		}

		return $days;
	}

	// TODO: consider moving createSnapshot to separate class?
	/**
	 * @return SprintSnapshot
	 */
	public function createSnapshot()
	{
		$phabricator = App::make('phabricator');
		$rawTaskData = $this->fetchTasks($phabricator);
		$rawTransactionData = $this->fetchTransactionData($phabricator, $rawTaskData);
		$taskDataProcessor = new TaskRawDataProcessor();
		$transactionDataProcessor = new TransactionRawDataProcessor();
		$tasks = $taskDataProcessor->process($rawTaskData);
		$transactions = $transactionDataProcessor->process($rawTransactionData);
		$columns = new ProjectColumnRepository($this->phid, $transactions, $phabricator);
		$presentationTask = (new TaskPresenter(
			(new StatusDispatcherFactory($this, $columns, $transactions))->getStatusDispatcher(),
			['ignore_estimates' => $this->ignore_estimates, 'ignored_columns' => $this->project->getIgnoredColumns()]
		))->render($tasks);
		$sumOfTasks = (new TaskList($presentationTask))->getTasksPerStatus();

		$data = [
			'tasks' => $this->getSnapshotTaskData($tasks),
			'transactions' => $this->getSnapshotTransactionData($transactions)
		];

		return SprintSnapshot::create([
			'sprint_id' => $this->id,
			'data' => json_encode($data),
			'total_points' => $sumOfTasks['total']['points'],
			'task_count' => $sumOfTasks['total']['tasks'],
		]);
	}

	private function fetchTasks(PhabricatorAPI $phabricator)
	{
		return (new TaskDataFetcher($phabricator))->fetchProjectTasks($this->phid);
	}

	private function fetchTransactionData(PhabricatorAPI $phabricator, array $tasks)
	{
		$taskIDs = array_map(function($task)
		{
			return $task['id'];
		}, $tasks);
		return (new TransactionLoader(
			new TransactionFilter(),
			$phabricator
		))->load($taskIDs);
	}

	private function getSnapshotTaskData(array $tasks)
	{
		return array_map(
			function(Task $task) {
				return $task->getData();
			},
			$tasks
		);
	}

	private function getSnapshotTransactionData(array $transactions)
	{
		$snapshotData = [];
		foreach ($transactions as $taskID => $taskTransactions)
		{
			$snapshotData[$taskID] = array_map(function(Transaction $transaction)
			{
				return $transaction->getData();
			}, $taskTransactions);
		}
		return $snapshotData;
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
