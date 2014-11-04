<?php
namespace Phragile;

use GuzzleHttp\Client;

class PhabricatorOAuth {
	public function requestAccessToken($authCode)
	{
		return with(new Client)->get($this->accessTokenURL($authCode))->json();
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
