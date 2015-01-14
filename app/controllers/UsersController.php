<?php

class UsersController extends BaseController {

	public function updateCertificate()
	{
		$user = Auth::user();
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