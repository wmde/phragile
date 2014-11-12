<?php
namespace Phragile\Providers;

class PhabricatorAPI {
	public function __construct(\ConduitClient $client)
	{
		$this->client = $client;
	}

	public function connect($user, $certificate)
	{
		return $this->client->callMethodSynchronous(
			'conduit.connect',
			[
				'client' => 'Phragile',
				'clientVersion' => 1,
				'user' => $user,
				'certificate' => $certificate
			]
		);
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
