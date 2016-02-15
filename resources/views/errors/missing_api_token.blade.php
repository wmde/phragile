@extends('layouts.default')

@section('title', 'Missing Conduit API Token')

@section('content')
    <h1>Missing Conduit API Token</h1>
    <p>
        You seem to be missing the Conduit API token for your Phragile bot.
        <br>Make sure the <code>PHRAGILE_BOT_API_TOKEN</code> setting is configured correctly in your <code>.env</code>-file.
    </p>
@stop
