var gulp = require('gulp'),
    less = require('gulp-less'),
    minify = require('gulp-minify-css'),
    uglify = require('gulp-uglify'),
    livereload = require('gulp-livereload'),
    bower = require('gulp-bower'),
    jshint = require('gulp-jshint');

var config = {
    componentsDir: 'app/assets/bower_components',
    lessFiles: 'app/assets/less/**/*.less',
    jsFiles: ['public/js/**/*', '!public/js/**/*.min.js', '!public/js/bootstrap-datepicker.js']
};

gulp.task('less', function () {
    return gulp.src(config.lessFiles)
            .pipe(less())
            .pipe(minify())
            .pipe(gulp.dest('public/css'))
            .pipe(livereload());
});

gulp.task('bower', function () {
    return bower();
});

gulp.task('js-libs', function () {
    return gulp.src([
            config.componentsDir + '/jquery/dist/jquery.min.js',
            config.componentsDir + '/bootstrap/dist/js/bootstrap.min.js',
            config.componentsDir + '/list.js/dist/list.min.js',
            config.componentsDir + '/bootstrap-datepicker/js/bootstrap-datepicker.js'
        ])
        .pipe(uglify())
        .pipe(gulp.dest('public/js'));
});

gulp.task('css', function () {
    return gulp.src([
            config.componentsDir + '/bootstrap-datepicker/css/datepicker.css'
        ])
        .pipe(minify())
        .pipe(gulp.dest('public/css'));
});

gulp.task('fonts', function () {
    return gulp.src([
            config.componentsDir + '/bootstrap/fonts/*'
        ])
        .pipe(gulp.dest('public/fonts'));
});

gulp.task('js', function () {
    return gulp.src(config.jsFiles)
        .pipe(livereload())
        .pipe(jshint())
        .pipe(jshint.reporter('default'));
});

gulp.task('watch', function () {
    livereload.listen();
    gulp.watch(config.lessFiles, ['less']);
    gulp.watch(config.jsFiles, ['js']);
});

gulp.task('default', ['bower', 'less', 'js-libs', 'js', 'css', 'fonts']);
