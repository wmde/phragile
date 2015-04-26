<?php
namespace Phragile;

use App;
use Phragile\ActionHandler\SprintLiveDataActionHandler;

class Phragile {

	public function newSprintLiveDataActionHandler()
	{
		return new SprintLiveDataActionHandler(App::make('phabricator'));
	}

}
