@extends('layouts.default')

@section('title', 'Phragile - Projects')

@section('content')
	</div> <!-- .container -->

	<div class="jumbotron front">
		<div class="container">
			<h1>Phragile</h1>
			<p>
				Generate sprint overviews for your Phabricator projects!
			</p>

			<p>
				Log in using your Phabricator account to create sprints for your projects on Phabricator. Phragile will then automatically generate burndown charts, status pie charts and a sortable and filterable sprint backlog for you.
			</p>

			<p>
				<div class="dropdown">
					<button class="btn btn-lg btn-primary" data-toggle="dropdown">
						Select a Project
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu" id="projects">
						@foreach($projects as $project)
							<li>
								{!! link_to_route(
									'project_path',
									$project->title,
									['project' => $project->slug]
								) !!}
							</li>
						@endforeach
					</ul>
				</div>

				@if(Auth::check() && Auth::user()->isInAdminList($_ENV['PHRAGILE_ADMINS']))
					@include('project.partials.create')
				@endif
			</p>
			<div class="clearfix"></div>
		</div>
	</div>
@stop
