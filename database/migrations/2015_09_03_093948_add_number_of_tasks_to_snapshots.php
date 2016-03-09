<?php

use Illuminate\Database\Migrations\Migration;

class AddNumberOfTasksToSnapshots extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('sprint_snapshots', function($t)
		{
			$t->integer('task_count');
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
			$t->dropColumn('task_count');
		});
	}

}
