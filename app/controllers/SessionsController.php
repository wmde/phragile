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

	public function logout()
	{
		Auth::logout();
		Flash::message('You are now logged out.');
		return Redirect::to('/');
	}

	private function loginFailed()
	{
		Flash::error('Login failed. Please try again.');
		return Redirect::to('/');
	}

	private function obtainAccessToken()
	{
		$response = (new PhabricatorOAuth)->requestAccessToken(Input::get('code'));
		return isset($response['access_token']) ? $response['access_token'] : null;
	}

	private function authenticate($accessToken)
	{
		$user = App::make('phabricator')->authenticate($accessToken);
		if ($user)
		{
			return $this->loginAndRedirect($user);
		}

		return $this->loginFailed();
	}

	private function loginAndRedirect($user)
	{
		Auth::login(User::firstOrCreate([
			'username' => $user['userName'],
			'phid' => $user['phid'],
		]));
		Flash::success("Hello ${user['userName']}, you are now logged in!");

		return Redirect::to('/');
	}
}
