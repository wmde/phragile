<?php

use Illuminate\Database\Migrations\Migration;

class CreateSprintsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sprints', function($t)
		{
			$t->string('phid', 100);
			$t->string('title', 255);
			$t->date('sprint_start');
			$t->date('sprint_end');
			$t->integer('project_id');
			$t->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('sprints');
	}

}
