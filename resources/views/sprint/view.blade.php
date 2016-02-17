@extends('layouts.default')

@section('title', 'Phragile - ' . (isset($snapshot) ? "Snapshot of {$sprint->title}" : $sprint->title))

@section('content')
	@include('project.partials.settings_form')
	@include('sprint.partials.settings_form')
	@include('sprint.partials.status_colors')

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

			@if(Auth::check() && Auth::user()->isInAdminList(env('PHRAGILE_ADMINS')))
				{!! Form::open(['route' => ['delete_sprint_path', $sprint->phabricator_id], 'method' => 'DELETE']) !!}
					{!! Form::submit(
						'&#xe014;', [
							'class' => 'btn btn-danger btn-lg glyphicon',
							'title' => 'Delete sprint',
							'onclick' => 'return confirm("Delete this sprint?")'
						])
					!!}
				{!! Form::close() !!}
			@endif
		</span>

		<a href="{{ env('PHABRICATOR_URL') }}project/view/{{ $sprint->phabricator_id }}" class="btn btn-default" title="Go to Phabricator" target="_blank">
			<img src="{{ URL::asset('/images/phabricator.png') }}" class="phabricator-icon"/>
		</a>
	</h1>

	<div class="row">
		<div class="col-md-8">
			<ul class="nav nav-tabs" id="pick-chart">
				<li role="presentation" data-graphs="ideal burndown"><a href="#!burndown">Burndown chart</a></li>
				<li role="presentation" data-graphs="scope burnup"><a href="#!burnup">Burnup chart</a></li>
			</ul>
			<div id="burndown">
				<table class="table table-condensed" id="graph-labels">
					<tbody>
					</tbody>
				</table>
			</div>
			<div id="chart-data" class="hidden">{!! json_encode($burnChartData) !!}</div>
		</div>

		<div class="col-md-4">
			<div class="dropdown" id="snapshots">
				@if(!$sprint->sprintSnapshots->isEmpty())
					<button class="btn btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
						{!! isset($snapshot) ? "Snapshot <span id='snapshot-date'>$snapshot->created_at</span>" : 'Live version' !!}
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
					{!! Form::open(['route' => ['create_snapshot_path', $sprint->phabricator_id]]) !!}
						{!! Form::submit(
							'Create snapshot', [
								'class' => 'btn btn-default btn-sm'
							])
						!!}
					{!! Form::close() !!}
				@endif

				@if(isset($snapshot) && Auth::check() && Auth::user()->isInAdminList(env('PHRAGILE_ADMINS')))
					{!! Form::open(['route' => ['delete_snapshot_path', $snapshot->id], 'method' => 'DELETE']) !!}
						{!! Form::submit(
							'&#xe014;', [
								'class' => 'btn btn-danger btn-sm glyphicon',
								'title' => 'Delete snapshot',
								'onclick' => 'return confirm("Delete this snapshot?")'
							])
						!!}
					{!! Form::close() !!}
				@endif
			</div>

			<table class="table status-table">
				@foreach($pieChartData as $status => $statusMeta)
					<tr class="{{ $status === 'total' ? 'reset-filter' : 'filter-backlog' }}" data-column="status" data-value="{{ $status === 'total' ? '' : $status }}">
						<th><span class="status-label {{ $statusMeta['cssClass'] }}">{{ $status }}</span></th>
						<td>{{ $statusMeta['tasks'] }} ({{ $statusMeta['points'] }} story points)</td>
					</tr>
				@endforeach
			</table>

			<div id="status-data" class="hidden">{{ json_encode(array_diff_key($pieChartData, ['total' => false])) }}</div>
			<div id="pie"></div>
		</div>
	</div>

	<button class="btn btn-default reset-filter" disabled="disabled">Show all tasks</button>
	<table id="backlog" class="table table-striped sprint-backlog">
		<thead>
			<tr>
				<th class="sort" data-sort="title">Title</th>
				<th class="sort" data-sort="priority">Priority</th>
				<th class="sort" data-sort="points">Story Points</th>
				<th class="sort" data-sort="assignee">Assignee</th>
				<th class="sort" data-sort="status">Status</th>
			</tr>
		</thead>

		<tbody class="list">
			@foreach($sprintBacklog as $task)
				<tr id="t{{ $task->getId() }}">
					<td>
						{!! link_to(
							env('PHABRICATOR_URL') . 'T' . $task->getId(),
							'#' . $task->getId(). ' ' . $task->getTitle(),
							[
								'class' => 'title',
								'target' => '_blank'
							]
						) !!}
					</td>

					<?php $priorityValue = Config::get('phabricator.MANIPHEST_PRIORITIES')[strtolower($task->getPriority())] ?>
					<td class="filter-backlog" data-column="priority" data-value="{{ $priorityValue }}">
						<span class="priority hidden">{{ $priorityValue }}</span>
						{{ $task->getPriority() }}
					</td>
					<td class="points">{{ $task->getPoints() }}</td>

					<td class="assignee filter-backlog" data-column="assignee" data-value="{{ $task->getAssigneeName() }}">
						{{ $task->getAssigneeName() }}
					</td>
					<td class="filter-backlog" data-column="status" data-value="{{ $task->getStatus() }}">
						<span class="status status-label {{ $task->getCssClass() }}">{{ $task->getStatus() }}</span>
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>
@stop

@section('optional_scripts')
	@parent

	{!! HTML::script('js/sprint_overview.js') !!}
@stop
