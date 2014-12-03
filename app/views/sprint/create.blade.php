@extends('layouts.default')

@section('content')
	<h1>Create sprint for {{ $project->title }}</h1>

	{{ Form::open(['method' => 'POST', 'route' => ['store_sprint_path', $project->slug]]) }}
		<div class="form-group">
			{{ Form::label('title', 'Title:') }}
			{{ Form::text('title', '', ['class' => 'form-control']) }}
		</div>

		<div class="form-group">
			{{ Form::label('sprint_start', 'Sprint start:') }}
			{{ Form::text('sprint_start', '', ['class' => 'form-control datepicker']) }}
		</div>

		<div class="form-group">
			{{ Form::label('sprint_end', 'Sprint end:') }}
			{{ Form::text('sprint_end', '', ['class' => 'form-control datepicker']) }}
		</div>

		{{ Form::submit('Create new sprint', ['class' => 'btn btn-primary']) }}
	{{ Form::close() }}
@stop

@section('optional_scripts')
	{{ HTML::script('js/bootstrap-datepicker.js') }}

	<script type="text/javascript">
		$('.datepicker').datepicker({ format: 'yyyy-mm-dd' });
	</script>
@stop

@section('optional_styles')
	{{ HTML::style('/css/datepicker.css') }}
@stop
