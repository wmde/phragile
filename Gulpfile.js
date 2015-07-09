var elixir = require('laravel-elixir');

elixir.config.sourcemaps = false;

elixir(function(mix) {
    mix.less('style.less', 'public/css/style.css');

    mix.scripts([
        '../assets/bower_components/d3/d3.js',
        'coordinate_system.js',
        'helpers.js',
        'chart_data.js',
        'graph.js',
        'progress_graph.js',
        'bar_chart.js',
        'main_chart.js',
        'pie_charts.js',
        '../assets/bower_components/list.js/dist/list.js',
        'sprint_backlog.js'
    ], 'public/js/sprint_overview.js', 'resources/js');

    mix.scripts([
        '../assets/bower_components/jquery/dist/jquery.js',
        '../assets/bower_components/bootstrap/dist/js/bootstrap.js'
    ], 'public/js/main.js', 'resources/js');

    mix.scripts(
        '../assets/bower_components/bootstrap-datepicker/js/bootstrap-datepicker.js',
        'public/js/datepicker.js', 'resources/js'
    ).styles(
        '../assets/bower_components/bootstrap-datepicker/css/datepicker3.css',
        'public/css/datepicker.css', 'resources/js'
    );
});
