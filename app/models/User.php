<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;

class User extends Eloquent implements UserInterface{

	// This is used for password authentication, recovery etc which we don't need.
	// Only using this because Auth::login won't work otherwise.
	use UserTrait;

	protected $fillable = ['phid', 'username'];
}
