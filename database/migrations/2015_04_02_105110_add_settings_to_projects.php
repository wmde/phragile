<?php

use Illuminate\Database\Migrations\Migration;

class AddSettingsToProjects extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('projects', function($t)
		{
			$t->string('closed_statuses');
			$t->boolean('workboard_mode')->default(false);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('projects', function($t)
		{
			$t->dropColumn('closed_statuses');
			$t->dropColumn('workboard_mode');
		});
	}

}
