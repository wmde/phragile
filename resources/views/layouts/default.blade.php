<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>@yield('title', 'Phragile')</title>
{!! HTML::style('css/style.css') !!}
@yield('optional_styles')
</head>
<body>

@include('flash::message')
@include('layouts.partials.header')

<div class="container">
    @yield('content')
</div>

{!! HTML::script('js/main.js') !!}
@yield('optional_scripts')

</body>
</html>
