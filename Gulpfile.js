var gulp = require('gulp'),
    less = require('gulp-less'),
    minify = require('gulp-minify-css');

gulp.task('less', function () {
    return gulp.src('app/assets/less/style.less')
            .pipe(less())
            .pipe(minify())
            .pipe(gulp.dest('public/css'));
});

gulp.task('watch', function () {
    gulp.watch('app/assets/less/**/*.less', ['less']);
})

gulp.task('default', ['watch']);
