<style type="text/css">
    @foreach($statusColors as $cssClass => $color)
        .{{str_replace(' ', '.', $cssClass)}} {
            background-color: {{$color}} !important;
            fill: {{$color}} !important;
        }
    @endforeach
</style>
