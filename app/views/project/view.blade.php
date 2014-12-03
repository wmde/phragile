@extends('layouts.default')

@section('content')
	<h1 class="sprint-overview-title">
		{{ $project->title }} Sprint Overview:
		<select id="sprint-select" class="sprint-select">
			@foreach($project->sprints as $sprint)
				@if($currentSprint && $currentSprint->id === $sprint->id)
					<option value="{{ $sprint->id }}" selected>Current Sprint ({{ $sprint->title }})</option>
				@else
					<option value="{{ $sprint->id }}">{{ $sprint->title }}</option>
				@endif
			@endforeach
		</select>
	</h1>
@stop
