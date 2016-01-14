<?php

use Phragile\PhabricatorAPI;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class User extends Eloquent implements AuthenticatableContract {

	// This is used for password authentication, recovery etc which we don't need.
	// Only using this because Auth::login won't work otherwise.
	use Authenticatable;

	protected $fillable = ['phid', 'username'];

	private $phabricatorURL;

	public function setRememberToken($token) {
		// Workaround: Auth::logout breaks with the default behavior since we're using OAuth.
	}

	/**
	 * @param string $url
	 */
	public function setPhabricatorURL($url)
	{
		$this->phabricatorURL = $url;
	}

	public function certificateValid($certificate = null)
	{
		try
		{
			$phabricator = new PhabricatorAPI(new ConduitClient($this->phabricatorURL));
			$phabricator->connect($this->username, $certificate ?: $this->conduit_certificate);
		} catch (ConduitClientException $e)
		{
			return false;
		}

		return true;
	}

	public function apiTokenValid($token = null)
	{
		try
		{
			$client = new ConduitClient($this->phabricatorURL);
			$client->setConduitToken($token);
			$client->callMethodSynchronous('user.whoami', []);
		} catch (ConduitClientException $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * @param string $admins - Comma separated Phabricator user names
	 * @return bool
	 */
	public function isInAdminList($admins)
	{
		return in_array(
			strtolower($this->username),
			array_map('strtolower', array_map('trim', explode(',', $admins)))
		);
	}
}
