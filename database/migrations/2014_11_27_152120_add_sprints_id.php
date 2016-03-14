<?php

use Illuminate\Database\Migrations\Migration;

class AddSprintsId extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('sprints', function($t)
		{
			$t->increments('id');
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
			$t->dropColumn('id');
		});
	}

}
