@extends('layouts.default')

@section('title', "Phragile - Project not found")

@section('content')
    <h1>Project not found</h1>
    <p>
        It looks like this project does not exist.
    </p>
    <p>
        @include('project.partials.select')
    </p>
@stop
