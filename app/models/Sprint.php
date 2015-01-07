<?php

use Phragile\PhabricatorAPI;

class Sprint extends Eloquent {

	protected $fillable = ['phid', 'phabricator_id', 'project_id', 'title', 'sprint_start', 'sprint_end'];

	private $phabricatorError = null;
	private $days = null;

	public function project()
	{
		return $this->belongsTo('Project');
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

	public function save(array $options = [])
	{
		return $this->createPhabricatorProject() && parent::save($options);
	}

	public function getPhabricatorError()
	{
		return $this->phabricatorError;
	}

	/**
	 * Returns a list of dates from sprint_start to sprint_end
	 *
	 * @return array
	 */
	public function getDays()
	{
		if ($this->days !== null)
		{
			return $this->days;
		}

		$this->days = [];

		for ($day = strtotime($this->sprint_start);
			 $day <= strtotime($this->sprint_end);
			 $day += 60*60*24)
		{
			$this->days[] = $day;
		}

		return $this->days;
	}

	private function createPhabricatorProject()
	{
		$phabricator = new PhabricatorAPI(new ConduitClient($_ENV['PHABRICATOR_URL']));
		$user = Auth::user();
		try
		{
			$phabricator->connect($user->username, $user->conduit_certificate);
			$response = $phabricator->createProject($this->title);
		} catch (ConduitClientException $e)
		{
			$this->phabricatorError = 'Failed to create a Phabricator for the sprint: ' . $e->getMessage();
			return false;
		}

		$this->phid = $response['phid'];
		$this->phabricator_id = $response['id'];

		return true;
	}
}
