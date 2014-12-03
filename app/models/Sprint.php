<?php

class Sprint extends Eloquent {

	protected $fillable = ['phid', 'project_id', 'title', 'sprint_start', 'sprint_end'];
	private $rules = [
		'title' => 'required',
		'sprint_start' => 'required|date_format:"Y-m-d"',
		'sprint_end' => 'required|date_format:"Y-m-d"'
	];

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
}
