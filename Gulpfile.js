var elixir = require('laravel-elixir');

elixir(function(mix) {
    mix.less('style.less');

    mix.scripts([
        '../assets/bower_components/d3/d3.js',
        'burndown.js',
        'pie_charts.js',
        '../assets/bower_components/list.js/dist/list.js',
        'sprint_backlog.js'
    ], 'public/js/sprint_overview.js');

    mix.scripts([
        '../assets/bower_components/jquery/dist/jquery.js',
        '../assets/bower_components/bootstrap/dist/js/bootstrap.js'
    ], 'public/js/main.js');

    mix.scripts(
        '../assets/bower_components/bootstrap-datepicker/js/bootstrap-datepicker.js',
        'public/js/datepicker.js'
    ).styles(
        '../assets/bower_components/bootstrap-datepicker/css/datepicker3.css',
        'public/css/datepicker.css'
    );
});
