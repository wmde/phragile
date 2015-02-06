@extends('layouts.default')

@section('title', 'Phragile - ' . (isset($snapshot) ? "Snapshot of {$sprint->title}" : $sprint->title))

@section('content')
	<h1 class="sprint-overview-title">
		{{ $sprint->project->title }}
		{{ isset($snapshot) ? "Snapshot $snapshot->created_at" : 'Sprint Overview' }}
		<span class="dropdown">
			<button class="btn btn-lg dropdown-toggle" type="button" data-toggle="dropdown">
				{{ $sprint->title }}
                <span class="caret"></span>
			</button>
			<ul class="dropdown-menu">
				@foreach($sprint->project->sprints as $s)
					<li class="{{ $s->id === $sprint->id ? 'active' : '' }}">
						<a href="{{ route('sprint_path', $s->phabricator_id) }}">
							@if($currentSprint && $currentSprint->id === $s->id && $currentSprint->isActive())
								Current sprint ({{ $s->title }})
							@else
								{{ $s->title }}
							@endif
						</a>
					</li>
				@endforeach
			</ul>
		</span>

		<a href="{{ $_ENV['PHABRICATOR_URL'] }}project/view/{{ $sprint->phabricator_id }}" class="btn btn-default" title="Go to Phabricator" target="_blank">
			<img src="/images/phabricator.png" class="phabricator-icon"/>
		</a>
	</h1>

	<?php $closedPerDay = $burndown->getPointsClosedPerDay() ?>
	<div class="row">
		<div class="col-md-8">
			<div id="burndown-data"
				 class="hidden"
				 data-total="{{ $taskList->getTasksPerStatus()['total']['points'] }}"
				 data-before="{{ $burndown->getPointsClosedBeforeSprint() }}">
				{{ json_encode($burndown->getPointsClosedPerDay()) }}
			</div>

			<table class="table table-condensed" id="graph-labels">
				<tbody>
					<tr class="actual">
						<td>Actual points</td>
						<td class="graph-value" id="actual-progress"></td>
					</tr>
					<tr class="ideal">
						<td>Ideal points</td>
						<td class="graph-value" id="ideal-progress"></td>
					</tr>
				</tbody>
			</table>
			<div id="burndown"></div>
		</div>

		<div class="col-md-4">
			<div class="dropdown" id="snapshots">
				@if(!$sprint->sprintSnapshots->isEmpty())
					<button class="btn btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
						{{ isset($snapshot) ? "Snapshot $snapshot->created_at" : 'Live version' }}
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu">
						<li class="{{ isset($snapshot) ? '' : 'active' }}">
							{!! link_to_route('sprint_live_path', 'Live version', ['sprint' => $sprint->phabricator_id]) !!}
						</li>

						@foreach($sprint->sprintSnapshots as $sprintSnapshot)
							<li class="{{ isset($snapshot) && $snapshot->id === $sprintSnapshot->id ? 'active' : '' }}">
								{!! link_to_route('snapshot_path', $sprintSnapshot->created_at, ['snapshot' => $sprintSnapshot->id]) !!}
							</li>
						@endforeach
					</ul>
				@endif

				@if(!isset($snapshot) && Auth::check())
					<a class="btn btn-default btn-sm" href="{{ route('create_snapshot_path', $sprint->phabricator_id) }}">
						Create snapshot
					</a>
				@endif

				@if(isset($snapshot) && Auth::check())
					{!! link_to_route(
						'delete_snapshot_path',
						'',
						['snapshot' => $snapshot->id],
						[
							'class' => 'btn btn-danger btn-sm glyphicon glyphicon-remove',
							'title' => 'Delete snapshot',
							'onclick' => 'return confirm("Delete this snapshot?")'
						]
					) !!}
				@endif
			</div>


			<?php $tasksPerStatus = $taskList->getTasksPerStatus() ?>
			<table class="table status-table">
				@foreach($tasksPerStatus as $status => $numbers)
					<tr class="filter-backlog" data-column="status" data-value="{{ $status === 'total' ? '' : $status }}">
						<th><span class="status-label {{ $status }}">{{ $status }}</span></th>
						<td>{{ $numbers['tasks'] }} ({{ $numbers['points'] }} story points)</td>
					</tr>
				@endforeach
			</table>

			<div id="status-data" class="hidden">{{ json_encode(array_diff_key($tasksPerStatus, ['total' => false])) }}</div>
			<div id="pie"></div>
		</div>
	</div>

	<button id="reset-filter" class="btn btn-default" disabled="disabled">Show all tasks</button>
	<table id="backlog" class="table table-striped sprint-backlog">
		<thead>
			<tr>
				<th class="sort" data-sort="title">Title</th>
				<th class="sort" data-sort="priority">Priority</th>
				<th class="sort" data-sort="points">Story Points</th>
				<th class="sort" data-sort="status">Status</th>
			</tr>
		</thead>

		<tbody class="list">
			@foreach($taskList->getTasks() as $task)
				<tr>
					<td>
						{!! link_to(
							$_ENV['PHABRICATOR_URL'] . 'T' . $task['id'],
							$task['title'],
							[
								'class' => 'title',
								'target' => '_blank'
							]
						) !!}
					</td>

					<?php $priorityValue = Config::get('phabricator.MANIPHEST_PRIORITIES')[strtolower($task['priority'])] ?>
					<td class="filter-backlog" data-column="priority" data-value="{{ $priorityValue }}">
						<span class="priority hidden">{{ $priorityValue }}</span>
						{{ $task['priority'] }}
					</td>
					<td class="points">{{ $task['story_points'] }}</td>
					<td class="status filter-backlog" data-column="status" data-value="{{ $task['status'] }}">
						<span class="status-label {{ $task['status'] }}">{{ $task['status'] }}</span>
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>
@stop

@section('optional_scripts')
	{!! HTML::script('js/d3.min.js') !!}
	{!! HTML::script('js/burndown.js') !!}
	{!! HTML::script('js/pie_charts.js') !!}
	{!! HTML::script('js/list.min.js') !!}
	{!! HTML::script('js/sprint_backlog.js') !!}
@stop
