<?php
namespace Phragile\Providers;

class PhabricatorAPI {
	public function __construct(\ConduitClient $client)
	{
		$this->client = $client;
	}

	public function authenticate($accessToken)
	{
		$response = $this->client->callMethodSynchronous(
			"user.whoami?access_token=$accessToken",
			[]
		);

		return isset($response['phid']) ? $response : null;
	}
}
