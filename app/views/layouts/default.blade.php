<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Phragile</title>
{{ HTML::style('/css/style.css') }}
</head>
<body>

@include('flash::message')
@include('layouts.partials.header')

<div class="container">
    @yield('content')
</div>

{{ HTML::script('js/jquery.min.js') }}
{{ HTML::script('js/bootstrap.min.js') }}

</body>
</html>
