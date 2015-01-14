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

		<a href="{{ $_ENV['PHABRICATOR_URL'] }}project/view/{{ $sprint->phabricator_id }}" title="Go to Phabricator" target="_blank">
			<span class="glyphicon glyphicon-new-window phab-link"></span>
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
			<div id="burndown"></div>
		</div>

		<div class="col-md-4">
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
						{{ link_to(
							$_ENV['PHABRICATOR_URL'] . 'T' . $task['id'],
							$task['title'],
							[
								'class' => 'title',
								'target' => '_blank'
							]
						) }}
					</td>

					<?php $priorityValue = $_ENV['MANIPHEST_PRIORITY_MAPPING.' . strtolower($task['priority'])] ?>
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
	{{ HTML::script('js/d3.min.js') }}
	{{ HTML::script('js/burndown.js') }}
	{{ HTML::script('js/pie_charts.js') }}
	{{ HTML::script('js/list.min.js') }}
	{{ HTML::script('js/sprint_backlog.js') }}
@stop
