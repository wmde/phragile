<nav class="navbar navbar-default">
	<div class="container">
		{{ link_to(URL::to('/'), 'Phragile', ['class' => 'navbar-brand']) }}

		<ul class="nav navbar-nav navbar-right">
			<li>
				{{ link_to(
					$_ENV['PHABRICATOR_URL'] . 'oauthserver/auth/?' . http_build_query([
						 'response_type' => 'code',
						 'client_id' => $_ENV['OAUTH_CLIENT_ID'],
						 'redirect_uri' => route('login_path'),
					]),
					'Log in using Phabricator'
				) }}
			</li>
		</ul>
	</div>
</nav>
