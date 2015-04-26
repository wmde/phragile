<?php
namespace Phragile;

use App;
use Phragile\ActionHandler\SprintLiveDataActionHandler;

class Phragile {

	/**
	 * @return self
	 */
	public static function getGlobalInstance()
	{
		return new self();
	}

	public function newSprintLiveDataActionHandler()
	{
		return new SprintLiveDataActionHandler(App::make('phabricator'));
	}

}
