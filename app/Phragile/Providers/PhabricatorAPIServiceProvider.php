<?php
namespace Phragile\Providers;

use Illuminate\Support\ServiceProvider;

class PhabricatorAPIServiceProvider extends ServiceProvider {
	public function register()
	{
		$this->app->singleton('phabricator', function() {
			return new PhabricatorAPI(new \ConduitClient($_ENV['PHABRICATOR_URL']));
		});
	}
}
