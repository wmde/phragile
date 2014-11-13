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
						<li>{{ link_to_route('logout_path', 'Logout') }}</li>
					</ul>
				</li>

				<div class="modal fade" id="conduit-modal">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
								<h4 class="modal-title">Conduit certificate</h4>
							</div>
							{{ Form::model(Auth::user(), ['route' => 'conduit_certificate_path', 'method' => 'PUT']) }}
								<div class="modal-body">
									<p>
										Copy and paste your Conduit certificate from Phabricator here.
										You can find it at
										{{ link_to(
											$_ENV['PHABRICATOR_URL'] . '/settings/panel/conduit/',
											'Phabricator &gt; Settings &gt; Conduit Certificate'
										) }}
									</p>
									<p>
										{{ Form::textarea(
											'conduit_certificate',
											null,
											[
												'class' => 'form-control',
												'rows' => 4,
												'placeholder' => 'Conduit Certificate',
											]
										) }}
									</p>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
									<button type="submit" class="btn btn-primary">Submit</button>
								</div>
							{{ Form::close() }}
						</div>
					</div>
				</div>
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
