<?php
namespace Phragile\Providers;

use Illuminate\Support\ServiceProvider;
use Phragile\PhabricatorAPI;

class PhabricatorAPIServiceProvider extends ServiceProvider {
	public function register()
	{
		$this->app->singleton('phabricator', function() {
			$phabricator = new PhabricatorAPI(new \ConduitClient(env('PHABRICATOR_URL')));
			$phabricator->connect(env('PHRAGILE_BOT_NAME'), env('PHRAGILE_BOT_CERTIFICATE'));

			return $phabricator;
		});
	}
}
