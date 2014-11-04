<?php

use Phragile\PhabricatorOAuth;

class SessionsController extends BaseController {

	public function login()
	{
		$accessToken = $this->obtainAccessToken();
		if (!$accessToken)
		{
			return $this->loginFailed();
		}

		return $this->authenticate($accessToken);
	}

	private function loginFailed()
	{
		Flash::error('Login failed. Please try again.');
		return Redirect::to('/');
	}

	private function obtainAccessToken()
	{
		$response = with(new PhabricatorOAuth())->requestAccessToken(Input::get('code'));
		return isset($response['access_token']) ? $response['access_token'] : null;
	}

	private function authenticate($accessToken)
	{
		$user = App::make('phabricator')->authenticate($accessToken);
		if ($user)
		{
			Flash::success("Hello ${user['realName']}, you are now logged in!");
			return Redirect::to('/');
		}

		return $this->loginFailed();
	}
}
