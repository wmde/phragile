<?php

class UsersController extends Controller {

	public function updateAPIToken()
	{
		$user = Auth::user();
		$user->setPhabricatorURL(env('PHABRICATOR_URL'));
		$token = Input::get('conduit_api_token');

		if ($user->apiTokenValid($token))
		{
			$user->conduit_api_token = Input::get('conduit_api_token');
			$user->save();

			Flash::success('Added your Conduit API Token.');
		} else
		{
			Flash::error('The submitted Conduit API Token was invalid.');
		}

		return Redirect::back();
	}
}
