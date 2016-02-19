<?php namespace App\Console\Commands;

use App\Console\Commands\Lib\SnapshotDataConverter;
use Illuminate\Console\Command;
use SprintSnapshot;

class MigrateSnapshots extends Command {

	protected $name = 'snapshots:migrate';
	protected $description = 'This command migrates all snapshots from the maniphest.query format to maniphest.search JSON.';

	public function fire()
	{
		foreach (SprintSnapshot::all() as $snapshot)
		{
			$snapshotData = json_decode($snapshot->data, true);
			if ($snapshotData['tasks'] && $this->isManiphestQueryFormat($snapshotData['tasks']))
			{
				$snapshotData['tasks'] = (new SnapshotDataConverter($snapshotData['tasks']))->convert();
				$snapshot->data = json_encode($snapshotData);
				$snapshot->save();
			}
		}
	}

	private function isManiphestQueryFormat(array $taskData)
	{
		return array_keys($taskData) !== range(0, count($taskData) - 1);
	}
}
