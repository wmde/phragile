<?php
namespace Phragile;

use App;
use Phragile\ActionHandler\SprintLiveDataActionHandler;
use Phragile\ActionHandler\SprintNotFoundActionHandler;
use Phragile\ActionHandler\SprintStoreActionHandler;
use \ConduitClient;

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

	public function newSprintStoreActionHandler()
	{
		return new SprintStoreActionHandler(
			new PhabricatorAPI(new ConduitClient($_ENV['PHABRICATOR_URL'])),
			App::make('phabricator')
		);
	}

	public function newSprintNotFoundActionHandler()
	{
		return new SprintNotFoundActionHandler(App::make('phabricator'));
	}
}
