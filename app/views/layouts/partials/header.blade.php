<nav class="navbar navbar-default" role="navigation">
	<div class="container">
		{{ link_to(URL::to('/'), 'Phragile', ['class' => 'navbar-brand']) }}

		<ul class="nav navbar-nav navbar-right">
			@if(Auth::check())
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
						Logged in as {{ Auth::user()->username }}
						<span class="caret"></span>
					</a>
					<ul class="dropdown-menu" role="menu">
						<li>
							{{ link_to(
								'#',
								'Conduit certificate',
								[
									'id' => 'conduit-certificate',
									'data-toggle' => 'modal',
									'data-target' => '#conduit-modal',
								]
							) }}
						</li>
						@if(in_array(Route::currentRouteName(), ['project_path', 'sprint_path']))
							<li>{{ link_to_route('create_sprint_path', 'New sprint', isset($project) ? $project->slug : $sprint->project->slug) }}</li>
						@endif
						<li>{{ link_to_route('logout_path', 'Logout') }}</li>
					</ul>
				</li>

				@include('layouts.partials.conduit_certificate_form')
			@else
				{{ link_to(
					$_ENV['PHABRICATOR_URL'] . 'oauthserver/auth/?' . http_build_query([
						 'response_type' => 'code',
						 'client_id' => $_ENV['OAUTH_CLIENT_ID'],
						 'redirect_uri' => route('login_path'),
					]),
					'Log in using Phabricator',
					['class' => 'btn btn-default navbar-btn btn-sm']
				) }}
			@endif
		</ul>
	</div>
</nav>
