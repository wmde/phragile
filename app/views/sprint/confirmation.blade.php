@extends('layouts.default')

@section('content')
	<h1>Successfully created "{{ $sprint->title }}"</h1>

	<ul>
		<li>
			{{ link_to(
				$_ENV['PHABRICATOR_URL'] . 'project/view/' . $sprint->phabricator_id,
				$sprint->title . ' on Phabricator'
			) }}
		</li>
		<li>
			{{ link_to_route(
				'sprint_path',
				$sprint->title . ' on Phragile',
				$sprint->phid
			) }}
		</li>
		<li>
			{{ link_to_route(
				'project_path',
				$sprint->project->title . ' on Phragile',
				$sprint->project->slug
			) }}
		</li>
	</ul>
@stop
