<?php

use Illuminate\Database\Migrations\Migration;

class AddIgnoredColumnsToProject extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('projects', function($t)
		{
			$t->string('ignored_columns');
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
			$t->dropColumn('ignored_columns');
		});
	}

}
