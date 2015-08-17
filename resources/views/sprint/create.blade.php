@extends('layouts.default')

@section('title', 'Phragile - Create a new sprint')

@section('content')
	<h1>Create sprint for <span id="project-title">{{ $project->title }}</span></h1>

	{!! Form::open(['method' => 'POST', 'route' => ['store_sprint_path', $project->slug]]) !!}
		<div class="form-group">
			{!! Form::label('sprint_start', 'Sprint start:') !!}
			{!! Form::text('sprint_start', '', ['class' => 'form-control datepicker start']) !!}
		</div>

		<div class="form-group">
			{!! Form::label('sprint_end', 'Sprint end:') !!}
			{!! Form::text('sprint_end', '', ['class' => 'form-control datepicker end']) !!}
		</div>

		<div class="form-group">
			{!! Form::label('title', 'Title:') !!}
			{!! Form::text('title', '', ['class' => 'form-control', 'id' => 'sprint-title']) !!}
		</div>

		{!! Form::submit('Create new sprint', ['class' => 'btn btn-primary']) !!}
	{!! Form::close() !!}
@stop

@include('sprint.partials.datepicker_assets')
