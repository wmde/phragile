<?php
namespace Phragile;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class PhabricatorOAuth {
	private $phabricatorURL;

	public function __construct($phabricatorURL)
	{
		$this->phabricatorURL = $phabricatorURL;
	}

	// TODO: This might fail in the future as Phabricator's OAuth implementation is supposed to redirect back to the
	//       client application and not just respond with a JSON object.
	public function requestAccessToken($authCode, $continue)
	{
		return $this->tryToGetJSON($this->accessTokenURL($authCode, $continue));
	}

	public function authenticate($accessToken)
	{
		$response = $this->tryToGetJSON(
			$this->phabricatorURL . 'api/user.whoami?' . http_build_query(
				['api.access_token' => $accessToken]
			)
		);

		return isset($response['result']) ? $response['result'] : null;
	}

	private function accessTokenURL($authCode, $continue)
	{
		return $this->phabricatorURL . 'oauthserver/token/?' . http_build_query([
			'client_id' => env('OAUTH_CLIENT_ID'),
			'client_secret' => env('OAUTH_CLIENT_SECRET'),
			'code' => $authCode,
			'grant_type' => 'authorization_code',
			'redirect_uri' => route('login_path', ['continue' => $continue])
		]);
	}

	private function tryToGetJSON($url)
	{
		try
		{
			return (new Client)->get($url)->json();
		} catch (ClientException $e)
		{
			return null;
		}
	}
}
