<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Phragile\Providers\PhabricatorAPI;

class User extends Eloquent implements UserInterface {

	// This is used for password authentication, recovery etc which we don't need.
	// Only using this because Auth::login won't work otherwise.
	use UserTrait;

	protected $fillable = ['phid', 'username'];

	public function setRememberToken($token) {
		// Workaround: Auth::logout breaks with the default behavior since we're using OAuth.
	}

	public function certificateValid($certificate)
	{
		try
		{
			$phabricator = new PhabricatorAPI(new ConduitClient($_ENV['PHABRICATOR_URL']));
			$phabricator->connect($this->username, $certificate);
		} catch (ConduitClientException $e)
		{
			return false;
		}

		return true;
	}
}
