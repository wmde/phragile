<?php

use Illuminate\Database\Migrations\Migration;

class AddSprintSettings extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('sprints', function($t)
		{
			$t->boolean('ignore_estimates')->default(false);
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
			$t->dropColumn('ignore_estimates');
		});
	}

}
