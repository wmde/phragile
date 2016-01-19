<?php
namespace Phragile\Providers;

use Illuminate\Support\ServiceProvider;
use Phragile\PhabricatorAPI;

class PhabricatorAPIServiceProvider extends ServiceProvider {
	public function register()
	{
		$this->app->singleton('phabricator', function() {
			if(env('PHRAGILE_BOT_API_TOKEN')) {
				$client = new \ConduitClient(env('PHABRICATOR_URL'));
				$client->setConduitToken(env('PHRAGILE_BOT_API_TOKEN'));
				$phabricator = new PhabricatorAPI($client);
			} else {
				$phabricator = new PhabricatorAPI(new \ConduitClient(env('PHABRICATOR_URL')));
				$phabricator->connect(env('PHRAGILE_BOT_NAME'), env('PHRAGILE_BOT_CERTIFICATE')); // still allow deprecated auth with certificate
			}
			return $phabricator;
		});
	}
}
