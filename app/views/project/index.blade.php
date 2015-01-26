@extends('layouts.default')

@section('content')
	<ul id="projects">
		@foreach($projects as $project)
			<li>
				{{ link_to_route(
					'project_path',
					$project->title,
					['project' => $project->slug]
				) }}
			</li>
		@endforeach
	</ul>

	@if(Auth::check() && Auth::user()->isAdmin())
		@include('project.partials.create')
	@endif
@stop
