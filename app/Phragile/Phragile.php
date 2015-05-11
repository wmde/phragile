<?php
namespace Phragile;

use App;
use Phragile\ActionHandler\SprintLiveDataActionHandler;
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
		// it's important not to use App::make('phabricator') because we specifically want a different
		// instance of PhabricatorAPI and not our singleton to connect with a user's Conduit certificate
		return new SprintStoreActionHandler(new PhabricatorAPI(new ConduitClient($_ENV['PHABRICATOR_URL'])));
	}
}
