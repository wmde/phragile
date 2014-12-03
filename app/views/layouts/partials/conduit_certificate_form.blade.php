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
