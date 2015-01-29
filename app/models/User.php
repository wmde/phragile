<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Phragile\PhabricatorAPI;

class User extends Eloquent implements UserInterface {

	// This is used for password authentication, recovery etc which we don't need.
	// Only using this because Auth::login won't work otherwise.
	use UserTrait;

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

	/**
	 * @param string $admins - Comma separated Phabricator user names
	 * @return bool
	 */
	public function isInAdminList($admins)
	{
		return in_array(
			strtolower($this->username),
			array_map('trim', explode(',', $admins))
		);
	}
}
