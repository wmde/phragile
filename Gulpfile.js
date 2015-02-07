var elixir = require('laravel-elixir');

elixir(function(mix) {
    mix.less('style.less')
        .version('css/style.css');
});
