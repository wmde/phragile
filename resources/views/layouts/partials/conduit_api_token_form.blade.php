<div class="modal fade" id="conduit-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title">Conduit API Token</h4>
			</div>
			{!! Form::model(Auth::user(), ['route' => 'conduit_api_token_path', 'method' => 'PUT']) !!}
				<div class="modal-body">
					<p>
						Paste a Phabricator Conduit API Token here. You can create and find your tokens at
						{!! link_to(
							env('PHABRICATOR_URL') . '/settings/panel/apitokens/',
							'Phabricator &gt; Settings &gt; Conduit API Tokens'
						) !!}
					</p>
					<p>
						{!! Form::text('conduit_api_token', null, ['class' => 'form-control']) !!}
					</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-primary">Submit</button>
				</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
