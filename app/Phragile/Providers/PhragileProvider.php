<?php
namespace Phragile\Providers;

use Phragile\Phragile;
use Illuminate\Support\ServiceProvider;

class PhragileProvider extends ServiceProvider {
	public function register()
	{
		$this->app->singleton('phragile', function()
		{
			return new Phragile();
		});
	}
}
