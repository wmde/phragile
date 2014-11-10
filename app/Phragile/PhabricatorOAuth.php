<?php
namespace Phragile;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class PhabricatorOAuth {

	// TODO: This might fail in the future as Phabricator's OAuth implementation is supposed to redirect back to the
	//       client application and not just respond with a JSON object.
	public function requestAccessToken($authCode)
	{
		try
		{
			return (new Client)->get($this->accessTokenURL($authCode))->json();
		} catch (ClientException $e)
		{
			return null;
		}
	}

	private function accessTokenURL($authCode)
	{
		return $_ENV['PHABRICATOR_URL'] . 'oauthserver/token/?' . http_build_query([
			'client_id' => $_ENV['OAUTH_CLIENT_ID'],
			'client_secret' => $_ENV['OAUTH_CLIENT_SECRET'],
			'code' => $authCode,
			'grant_type' => 'authorization_code',
			'redirect_uri' => route('login_path')
		]);
	}
}
