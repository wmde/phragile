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
	</h1>

	<table class="table table-striped">
		<tr>
			<th>Title</th>
			<th>Priority</th>
			<th>Story Points</th>
			<th>Status</th>
		</tr>

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
