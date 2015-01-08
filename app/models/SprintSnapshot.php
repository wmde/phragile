<?php

class SprintSnapshot extends Eloquent {

	public function sprint()
	{
		return $this->belongsTo('Sprint');
	}
}
