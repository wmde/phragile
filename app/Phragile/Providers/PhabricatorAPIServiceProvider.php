<?php
namespace Phragile\Providers;

use Illuminate\Support\ServiceProvider;
use Phragile\PhabricatorAPI;

class PhabricatorAPIServiceProvider extends ServiceProvider {
	public function register()
	{
		$this->app->singleton('phabricator', function()
		{
			$client = new \ConduitClient(env('PHABRICATOR_URL'));
			$client->setConduitToken(env('PHRAGILE_BOT_API_TOKEN'));
			return new PhabricatorAPI($client);
		});
	}
}
