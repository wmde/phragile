<?php

use Illuminate\Database\Migrations\Migration;

class AddDefaultValues extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function($table)
		{
			$table->string('conduit_certificate')->default('')->change();
		});
		Schema::table('projects', function($table)
		{
			$table->string('closed_statuses')->default('')->change();
			$table->string('ignored_columns')->default('')->change();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function($table)
		{
			$table->text('conduit_certificate')->default()->change();
		});
		Schema::table('projects', function($table)
		{
			$table->string('closed_statuses')->default()->change();
			$table->string('ignored_columns')->default()->change();
		});
	}

}
