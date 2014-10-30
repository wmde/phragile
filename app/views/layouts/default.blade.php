<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Phragile</title>
{{ HTML::style('/css/style.css') }}
</head>
<body>

@include('layouts.partials.header')

<div class="container">
    @yield('content')
</div>

</body>
</html>
