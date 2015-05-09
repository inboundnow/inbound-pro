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
    gulpIgnore = require('gulp-ignore'),
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

/**
 * Todo: move /shared to a pro folder and have all plugins share
 */
gulp.task('sync-lp', function () {

        return gulp.src(['../landing-pages/**']).pipe(gulp.dest('./core/landing-pages/'));
});
gulp.task('sync-cta', function () {

        return gulp.src(['../cta/**']).pipe(gulp.dest('./core/cta/'));
});
gulp.task('sync-leads', function () {

        return gulp.src(['../leads/**'])
        //.pipe(gulpIgnore.exclude(condition))
        .pipe(gulp.dest('./core/leads/'));
});

gulp.task('move-shared', function () {
        return gulp.src(['./core/cta/shared/**'])
        //.pipe(gulpIgnore.exclude(condition))
        .pipe(gulp.dest('./core/shared/'));
});


gulp.task('clean-lp', ['sync-lp'], function () {
    return gulp.src(['./core/landing-pages/node_modules/', './core/leads/shared/'], {read: false})
        .pipe(clean());
});

gulp.task('clean-cta', ['sync-cta', 'move-shared'], function () {
    return gulp.src(['./core/cta/node_modules/', './core/cta/shared/'], {read: false})
        .pipe(clean());
});

gulp.task('clean-leads', ['sync-leads'], function () {
    return gulp.src(['./core/leads/node_modules/', './core/landing-pages/shared/'], {read: false})
        .pipe(clean());
});



/* Sync all core plugins */
gulp.task('sync', ['clean-cta', 'clean-lp','clean-leads']);

gulp.task('default', [
    'lint',
    'clean',
    'scripts',
    'generateDocs'
    // 'test'
]);