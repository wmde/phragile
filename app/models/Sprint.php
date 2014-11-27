<?php

class Sprint extends Eloquent {

	protected $fillable = ['phid', 'project_id', 'title', 'sprint_start', 'sprint_end'];

	public function project()
	{
		return $this->belongsTo('Project');
	}
}
