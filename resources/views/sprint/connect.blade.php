@extends('layouts.default')

@section('title', 'Connect existing Phabricator project')

@section('content')
    <h1>Connect with Phragile</h1>
    <p>
        The sprint "{{ $phabricatorProject['name'] }}" exists on Phabricator but not on Phragile.
        You can use the following form to connect it with Phragile.
    </p>

    <div class="alert alert-warning" role="alert">
        Please make sure you put the new sprint into the correct Phragile project! If your project does not exist, please ask one of the Phragile admins to create it.
        @if(env('PROJECT_REQUEST_URL'))
            You can file a request here: {!! link_to(env('PROJECT_REQUEST_URL')) !!}
        @endif
    </div>

    {!! Form::open(['method' => 'POST', 'route' => ['connect_sprint_path']]) !!}
        {!! Form::text('title', $phabricatorProject['id'], ['class' => 'hidden']) !!}

        <div class="form-group">
            {!! Form::label('project', 'Project:') !!}
            {!! Form::select('project', $projects, ['class' => 'form-control']) !!}
        </div>

        @if($duration)
            {!! Form::text('sprint_start', $duration['start'], ['class' => 'hidden']) !!}
            {!! Form::text('sprint_end', $duration['end'], ['class' => 'hidden']) !!}
        @else
            <div class="form-group">
                {!! Form::label('sprint_start', 'Sprint start:') !!}
                {!! Form::text('sprint_start', '', ['class' => 'form-control datepicker start']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('sprint_end', 'Sprint end:') !!}
                {!! Form::text('sprint_end', '', ['class' => 'form-control datepicker end']) !!}
            </div>
        @endif

        {!! Form::submit('Connect this sprint with Phragile', ['class' => 'btn btn-primary']) !!}
    {!! Form::close() !!}
@stop

@include('sprint.partials.datepicker_assets')
