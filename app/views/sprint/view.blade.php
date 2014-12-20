@extends('layouts.default')

@section('content')
	<h1 class="sprint-overview-title">
		{{ $sprint->project->title }} Sprint Overview:
		<span class="dropdown">
			<button class="btn btn-lg dropdown-toggle" type="button" data-toggle="dropdown">
				{{ $sprint->title }}
                <span class="caret"></span>
			</button>
			<ul class="dropdown-menu">
				@foreach($sprint->project->sprints as $s)
					<li class="{{ $s->id === $sprint->id ? 'active' : '' }}">
						<a href="{{ route('sprint_path', $s->phabricator_id) }}">
							@if($currentSprint && $currentSprint->id === $s->id)
								Current sprint ({{ $s->title }})
							@else
								{{ $s->title }}
							@endif
						</a>
					</li>
				@endforeach
			</ul>
		</span>

		<a href="{{ $_ENV['PHABRICATOR_URL'] }}project/view/{{ $sprint->phabricator_id }}" title="Go to Phabricator">
			<span class="glyphicon glyphicon-new-window phab-link"></span>
		</a>
	</h1>

	<?php $closedPerDay = $burndown->closedPerDay() ?>
	<div class="row">
		<div class="col-md-8">
			<div id="burndown-data"
				 class="hidden"
				 data-total="{{ $taskList->getTasksPerStatus()['total']['points'] }}"
				 data-before="{{ $burndown->getPointsClosedBeforeSprint() }}">
				{{ json_encode($burndown->getPointsClosedPerDay()) }}
			</div>
			<div id="burndown"></div>
		</div>

		<div class="col-md-4">
			<?php $tasksPerStatus = $taskList->getTasksPerStatus() ?>

			<table class="table status-table">
				@foreach($tasksPerStatus as $status => $numbers)
					<tr>
						<th>{{ $status }}</th>
						<td>{{ $numbers['tasks'] }} ({{ $numbers['points'] }} story points)</td>
					</tr>
				@endforeach
			</table>

			<div id="status-data" class="hidden">{{ json_encode(array_diff_key($tasksPerStatus, ['total' => false])) }}</div>
			<div id="pie"></div>
		</div>
	</div>

	<table class="table table-striped sprint-backlog">
		<thead>
			<tr>
				<th>Title</th>
				<th>Priority</th>
				<th>Story Points</th>
				<th>Status</th>
			</tr>
		</thead>

		@foreach($taskList->getTasks() as $task)
			<tr>
				<td>{{ $task['title'] }}</td>
				<td>{{ $task['priority'] }}</td>
				<td>{{ $task['story_points'] }}</td>
				<td>{{ $task['status'] }}</td>
			</tr>
		@endforeach
	</table>
@stop

@section('optional_scripts')
	{{ HTML::script('js/d3.min.js') }}
	{{ HTML::script('js/burndown.js') }}
	{{ HTML::script('js/pie_charts.js') }}
@stop
