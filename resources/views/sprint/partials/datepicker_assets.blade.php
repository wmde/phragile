@section('optional_scripts')
	@parent

	{!! HTML::script('js/datepicker.js') !!}

	<script type="text/javascript">
		var settings = {
			format: 'yyyy-mm-dd',
			autoclose: true
		};

		$('.datepicker.start').datepicker(settings)
			.on('changeDate', function (e) {
				if ($('#sprint-title').val() === '') {
					$('#sprint-title').val(
						'§ '
						+ $('#project-title').text()
						+ '-Sprint-'
						+ e.format(settings.format)
					);
				}
			});

		$('.datepicker.end').datepicker(settings);
	</script>
@stop

@section('optional_styles')
	@parent

	{!! HTML::style('/css/datepicker.css') !!}
@stop
