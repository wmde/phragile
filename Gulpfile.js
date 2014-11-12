var gulp = require('gulp'),
    less = require('gulp-less'),
    minify = require('gulp-minify-css'),
    bower = require('gulp-bower');

var config = {
    componentsDir: 'app/assets/bower_components',
    lessFiles: 'app/assets/less/**/*.less'
};

gulp.task('less', function () {
    return gulp.src(config.lessFiles)
            .pipe(less())
            .pipe(minify())
            .pipe(gulp.dest('public/css'));
});

gulp.task('bower', function () {
    return bower();
});

gulp.task('watch', function () {
    gulp.watch(config.lessFiles, ['less']);
});

gulp.task('default', ['bower', 'less']);
