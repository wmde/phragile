<?php

class UsersController extends Controller {

	public function updateCertificate()
	{
		$user = Auth::user();
		$user->setPhabricatorURL(env('PHABRICATOR_URL'));
		$certificate = Input::get('conduit_certificate');

		if ($user->certificateValid($certificate))
		{
			$user->conduit_certificate = Input::get('conduit_certificate');
			$user->save();

			Flash::success('Added your Conduit certificate.');
		} else
		{
			Flash::error('The submitted Conduit certificate was invalid.');
		}

		return Redirect::back();
	}
}
