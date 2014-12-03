<?php

class Project extends Eloquent {

	protected $fillable = ['title', 'slug'];

	public function sprints()
	{
		return $this->hasMany('Sprint')->orderBy('sprint_start', 'desc');
	}

	public function currentSprint()
	{
		return Sprint::where('sprint_start', '<=', date('Y-m-d'))
			->where('project_id', $this->id)
			->orderBy('sprint_start', 'desc')
			->first();
	}
}
