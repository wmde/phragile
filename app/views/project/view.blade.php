@extends('layouts.default')

@section('title', "Phragile - {$project->title}")

@section('content')
	<h1>{{ $project->title }}</h1>
	<p>
		@if(Auth::check())
			{{ link_to_route(
				'create_sprint_path',
				'Create a new sprint',
				['project' => $project->slug]
			) }}
		@else
			There are no sprints for this project yet.
		@endif
	</p>
@stop
