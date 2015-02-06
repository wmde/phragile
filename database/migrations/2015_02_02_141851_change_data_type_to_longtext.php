<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDataTypeToLongtext extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$this->dropColumn();

		Schema::table('sprint_snapshots', function($t)
		{
			$t->longText('data');
		});
	}

	private function dropColumn()
	{
		Schema::table('sprint_snapshots', function($t)
		{
			$t->dropColumn('data');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$this->dropColumn();

		Schema::table('sprint_snapshots', function($t)
		{
			$t->dropColumn('data');
		});
	}

}
