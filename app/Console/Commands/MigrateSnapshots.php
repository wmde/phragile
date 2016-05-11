<?php namespace App\Console\Commands;

use App\Console\Commands\Lib\SnapshotTaskDataConverter;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use SprintSnapshot;

class MigrateSnapshots extends Command {

	protected $name = 'snapshots:migrate';
	protected $description = 'This command migrates all snapshots from the maniphest.query format to maniphest.search JSON.';

	public function fire()
	{
		$snapshotCount = SprintSnapshot::count();
		$batchSize = intval($this->input->getOption('batchSize'));

		if (!$this->input->getOption('force'))
		{
			$confirmation = $this->confirm(
				'Do you really want to migrate ' . $snapshotCount . ' snapshots in batches of ' . $batchSize . '?'
			);
			if (!$confirmation)
			{
				$this->line('Migration aborted.');
				return;
			}
		}

		$taskConverter = new SnapshotTaskDataConverter();
		$this->line('Migration in progress:');
		$i = 0;
		while (count($snapshots = $this->getSnapshotsPart($batchSize, $batchSize * $i)) !== 0)
		{
			foreach ($snapshots as $snapshot)
			{
				$snapshotData = json_decode($snapshot->data, true);
				if ($snapshotData['tasks'] && $taskConverter->needsConversion($snapshotData['tasks']))
				{
					$snapshotData['tasks'] = $taskConverter->convert($snapshotData['tasks']);
					$snapshot->data = json_encode($snapshotData);
					$snapshot->save();
				}
			}
			$i++;
			$this->line($this->getMigrationProgress($batchSize, $i, $snapshotCount) . '%');
		}
		$this->line('Migration finished!');
	}

	private function getMigrationProgress( $batchSize, $iteration, $snapshotCount)
	{
		if ($batchSize * $iteration >= $snapshotCount)
		{
			return 100;
		}
		return round($batchSize / $snapshotCount * $iteration, 2) * 100;
	}

	private function getSnapshotsPart($limit, $offset)
	{
		return SprintSnapshot::take($limit)->skip($offset)->get();
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production.'],
			['batchSize', null, InputOption::VALUE_OPTIONAL, 'The number of snapshots to migrate per iteration.', 100],
		];
	}
}
