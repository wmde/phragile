<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRealPHIDToSprints extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('sprints', function($t)
		{
			$t->renameColumn('phid', 'phabricator_id');
		});

		Schema::table('sprints', function($t)
		{
			$t->string('phid');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('sprints', function($t)
		{
			$t->dropColumn('phid');
			$t->renameColumn('phabricator_id', 'phid');
		});
	}

}
