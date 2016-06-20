/**
 * @author infinitydevphp <infinitydevphp@gmail.com>
 */

var gulp = require('gulp'),
    uglify = require('gulp-uglify'),
    rename = require('gulp-rename');

gulp.task('default', function () {
    return gulp.src([
        "./src/assets/js/multiple.validator.js"
    ])
        .pipe(uglify({outSourceMap: true}))
        .pipe(rename(function (path) {
            if (path.extname === '.js') {
                path.basename += '.min';
            }
        }))
        .pipe(gulp.dest('./src/assets/js/'));
});

