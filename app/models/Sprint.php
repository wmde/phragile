<?php

class Sprint extends Eloquent {

	public function project()
	{
		return $this->belongsTo('Project');
	}
}
