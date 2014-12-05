<?php

use Phragile\PhabricatorAPI;

class Sprint extends Eloquent {

	protected $fillable = ['phid', 'phabricator_id', 'project_id', 'title', 'sprint_start', 'sprint_end'];
	private $rules = [
		'title' => 'required',
		'sprint_start' => 'required|date_format:"Y-m-d"',
		'sprint_end' => 'required|date_format:"Y-m-d"'
	];
	private $phabricatorError = null;

	public function project()
	{
		return $this->belongsTo('Project');
	}

	public function validate()
	{
		return Validator::make(
			$this->getAttributes(),
			$this->rules
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
