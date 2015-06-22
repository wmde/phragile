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

	// created_at behaves a little strange thanks to the internal framework implementation.
	// If accessed directly it will always try connecting to the database which is tricky e.g. in unit tests.
	public function setCreatedAt($date)
	{
		$this->attributes['created_at'] = $date;
	}

	public function getCreatedAt()
	{
		return $this->attributes['created_at'];
	}
}
