@extends('layouts.default')

@section('title', 'Phragile - Stats')

@section('content')
</div> <!-- .container -->

<div class="jumbotron front">
    <div class="container">
        <h1>Project statistics</h1>
        @if(Auth::check() && Auth::user()->isInAdminList(env('PHRAGILE_ADMINS')))
            <p>Overall numbers</p>
            <ul>
                <li>Projects: {!! $projectCount !!}</li>
                <li>Sprints: {!! $sprintCount !!}</li>
            </ul>

            <p>Sprints per project</p>
            <ul>
                @foreach($sprintsPerProject as $projectSprints)
                    <li>{!! $projectSprints[0] .': ' . $projectSprints[1] !!}</li>
                @endforeach
            </ul>
        @else
            <p>
                Log in using your Phabricator account to see usage statistics.
            </p>
        @endif
    </div>
    <div class="clearfix"></div>
</div>
@stop
