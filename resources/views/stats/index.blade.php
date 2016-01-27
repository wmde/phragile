@extends('layouts.default')

@section('title', 'Phragile - Stats')

@section('content')
</div> <!-- .container -->

<div class="jumbotron front">
    <div class="container">
        <h1>Project statistics</h1>

        <p>Overall numbers</p>
        <table class="table table-striped">
            <thead>
            <tr>
                <th width="50%">Name</th>
                <th>Value</th>
            </tr>
            </thead>
            <tbody class="list">
            <tr>
                <td>Projects</td>
                <td>{!! $projects->count() !!}</td>
            </tr>
            <tr>
                <td>Sprints</td>
                <td>{!! $sprintCount !!}</td>
            </tr>
            </tbody>
        </table>

        <p>Sprints per project</p>
        <table class="table table-striped">
            <thead>
            <tr>
                <th width="50%">Title</th>
                <th>Count</th>
            </tr>
            </thead>
            <tbody class="list">
            @foreach($projects->get() as $project)
                <tr>
                    <td>
                        {!! link_to_route(
                            'project_path',
                            $project->title,
                            ['project' => $project->slug]
                        ) !!}
                    </td>
                    <td>{{ $project->sprints->count() }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="clearfix"></div>
</div>
@stop
