<?php
namespace Phragile\Providers;

use Illuminate\Support\ServiceProvider;
use Phragile\PhabricatorAPI;

class PhabricatorAPIServiceProvider extends ServiceProvider {
	public function register()
	{
		$this->app->singleton('phabricator', function() {
			$phabricator = new PhabricatorAPI(new \ConduitClient($_ENV['PHABRICATOR_URL']));
			$phabricator->connect($_ENV['PHRAGILE_BOT_NAME'], $_ENV['PHRAGILE_BOT_CERTIFICATE']);

			return $phabricator;
		});
	}
}
