/* https://gist.github.com/demisx/9512212 excample
http://willi.am/blog/2014/08/16/gulp-automation-path-abstraction/

Good example: https://gist.github.com/samuelhorn/8743217 */
var gulp = require('gulp'),
    karma = require('gulp-karma'),
    jshint = require('gulp-jshint'),
    stylish = require('jshint-stylish'),
    header = require('gulp-header'),
    concat = require('gulp-concat'),
    uglify = require('gulp-uglify'),
    plumber = require('gulp-plumber'),
    clean = require('gulp-clean'),
    rename = require('gulp-rename'),
    copy = require('gulp-copy'),
    markdox = require("gulp-markdox"),
    //phplint = require('phplint').lint,
    package = require('./package.json');


//gulp.task('phplint', function(cb) {
    //phplint(['src/**/*.php'], {
 //   phplint(['calls-to-action.php'], {
//        limit: 10
 //   }, function(err, stdout, stderr) {
 //       if (err) {
   //         console.log(err);
  ////          cb(err);
   //         process.exit(1);
   //     }
   //     cb();
  //  });
//});


/* Watch Files For Changes */
gulp.task('watch', function() {
    //gulp.watch('shared/assets/js/frontend/analytics-src/*.js', ['lint', 'scripts']);
    gulp.watch('shared/assets/js/frontend/analytics-src/*.js', ['default']);
    //gulp.watch('scss/*.scss', ['sass']);
});
gulp.task('sync-lp', function () {
        //return gulp.src(['some/other/folders/src/public/**/*', 'some/other/folders/src/vendor/**/*'], {
        //    base: 'other'
        //}).pipe(gulp.dest('build'));
        return gulp.src(['../landing-pages/**']).pipe(gulp.dest('./core/landing-pages/'));
});
gulp.task('sync-cta', function () {
        //return gulp.src(['some/other/folders/src/public/**/*', 'some/other/folders/src/vendor/**/*'], {
        //    base: 'other'
        //}).pipe(gulp.dest('build'));
        return gulp.src(['../cta/**']).pipe(gulp.dest('./core/cta/'));
});
gulp.task('sync-leads', function () {
        //return gulp.src(['some/other/folders/src/public/**/*', 'some/other/folders/src/vendor/**/*'], {
        //    base: 'other'
        //}).pipe(gulp.dest('build'));
        return gulp.src(['../leads/**']).pipe(gulp.dest('./core/leads/'));
});

/* Sync all core plugins */
gulp.task('sync', ['sync-cta', 'sync-lp','sync-leads']);

gulp.task('default', [
    'lint',
    'clean',
    'scripts',
    'generateDocs'
    // 'test'
]);