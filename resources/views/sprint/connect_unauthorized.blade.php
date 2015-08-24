@extends('layouts.default')

@section('title', 'Connect existing Phabricator project')

@section('content')
    <h1>Connect with Phragile</h1>
    <p>
        The sprint "{{ $phabricatorProject['name'] }}" exists on Phabricator but not on Phragile.
        Please log in to connect the sprint with Phragile.
    </p>
@stop
