<?php

class SprintSnapshot extends Eloquent {
	protected $fillable =  ['data', 'sprint_id'];

	/**
	 * @return Sprint
	 */
	public function sprint()
	{
		return $this->belongsTo('Sprint');
	}
}
