<?php

class SprintSnapshot extends Eloquent {
	protected $fillable =  ['data', 'sprint_id', 'created_at', 'total_points'];

	/**
	 * @return Sprint
	 */
	public function sprint()
	{
		return $this->belongsTo('Sprint');
	}
}
