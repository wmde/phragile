<?php

class Project extends Eloquent {

	protected $fillable = ['title', 'slug'];

	public function sprints()
	{
		return $this->hasMany('Sprint')->orderBy('sprint_start', 'desc');
	}
}
