<nav class="navbar navbar-inverse" role="navigation">
	<div class="container">
		<div class="navbar-header">
			<a class="navbar-brand" href="/">
				<img src="/images/phragile_logo_white.svg" alt="Phragile logo"/>
			</a>
		</div>

		<ul class="nav navbar-nav navbar-right">
			@if(Auth::check())
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
						Logged in as {!! Auth::user()->username !!}
						<span class="caret"></span>
					</a>
					<ul class="dropdown-menu" role="menu">
						<li>
							{!! link_to(
								'#',
								'Conduit certificate',
								[
									'id' => 'conduit-certificate',
									'data-toggle' => 'modal',
									'data-target' => '#conduit-modal',
								]
							) !!}
						</li>
						@if(in_array(Route::currentRouteName(), ['project_path', 'sprint_path', 'sprint_live_path', 'snapshot_path']) && isset($sprint))
							<li>{!! link_to_route('create_sprint_path', 'New sprint', isset($project) ? $project->slug : $sprint->project->slug) !!}</li>
							<li><a id="project-settings" href="#" data-toggle="modal" data-target="#project-settings-modal">Project settings</a></li>
							<li><a id="sprint-settings" href="#" data-toggle="modal" data-target="#sprint-settings-modal">Sprint settings</a></li>
						@endif
						<li>{!! link_to_route('logout_path', 'Logout', ['continue' => Request::path()]) !!}</li>
					</ul>
				</li>

				@include('layouts.partials.conduit_certificate_form')
			@else
				{!! link_to(
					env('PHABRICATOR_URL') . 'oauthserver/auth/?' . http_build_query([
						'response_type' => 'code',
						'client_id' => env('OAUTH_CLIENT_ID'),
						'redirect_uri' => route('login_path', ['continue' => Request::path()]),
						'scope' => 'whoami',
					]),
					'Log in using Phabricator',
					['class' => 'btn btn-default navbar-btn btn-sm']
				) !!}
			@endif
		</ul>
	</div>
</nav>
