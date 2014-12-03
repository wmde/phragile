@extends('layouts.default')

@section('content')
	<h1>{{ $project->title }}</h1>
	{{ HTML::ul($project->sprints) }}
@stop
