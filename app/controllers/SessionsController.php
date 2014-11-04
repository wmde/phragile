<?php

use Phragile\PhabricatorOAuth;

class SessionsController extends BaseController {

	public function login()
	{
		$response = with(new PhabricatorOAuth())->requestAccessToken(Input::get('code'));
		if (isset($response['access_token']))
		{
			$user = App::make('phabricator')->authenticate($response['access_token']);
			if ($user)
			{
				return "Hello, ${user['realName']}";
			}
		}
	}
}
