<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Sprint;

class CreateSnapshots extends Command {

	protected $name = 'snapshots:create';
	protected $description = 'This command creates snapshots of all sprints in Phragile.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		foreach (Sprint::all() as $sprint)
		{
			$sprint->createSnapshot();
		}
	}
}
