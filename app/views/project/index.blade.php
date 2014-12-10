@extends('layouts.default')

@section('content')
	<ul>
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
@stop
