<?php

class SprintSnapshot extends Eloquent {
	protected $fillable =  ['data', 'sprint_id'];

	public function sprint()
	{
		return $this->belongsTo('Sprint');
	}
}
