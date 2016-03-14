<?php

use Illuminate\Database\Migrations\Migration;

class AddTotalPointsToSnapshots extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('sprint_snapshots', function($t)
		{
			$t->integer('total_points');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('sprint_snapshots', function($t)
		{
			$t->dropColumn('total_points');
		});
	}

}
