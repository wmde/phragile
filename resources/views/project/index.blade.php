@extends('layouts.default')

@section('title', 'Phragile - Projects')

@section('content')
	</div> <!-- .container -->

	<div class="jumbotron front">
		<a href="https://github.com/wmde/phragile"><img style="position: absolute; top: 0; right: 0; border: 0;" src="https://camo.githubusercontent.com/38ef81f8aca64bb9a64448d0d70f1308ef5341ab/68747470733a2f2f73332e616d617a6f6e6177732e636f6d2f6769746875622f726962626f6e732f666f726b6d655f72696768745f6461726b626c75655f3132313632312e706e67" alt="Fork me on GitHub" data-canonical-src="https://s3.amazonaws.com/github/ribbons/forkme_right_darkblue_121621.png"></a>

		<div class="container">
			<h1>Phragile</h1>
			<p>
				Generate sprint overviews for your Phabricator projects!
			</p>

			<p>
				Log in using your Phabricator account to create sprints for your projects on Phabricator. Phragile will then automatically generate burndown charts, pie charts and a sortable and filterable sprint backlog for you.
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

	<footer class="container">
		<hr/>
		<p>
			Built by <a href="http://github.com/jakobw">Jakob Warkotsch</a> as a thesis project at <a href="http://fu-berlin.de">Freie Universität Berlin</a> in cooperation with <a href="http://wikimedia.de">Wikimedia Deutschland</a>
		</p>
	</footer>
@stop
