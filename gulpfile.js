var gulp    = require('gulp'),
    karma   = require('gulp-karma'),
    jshint  = require('gulp-jshint'),
    stylish = require('jshint-stylish'),
    header  = require('gulp-header'),
    concat  = require('gulp-concat'),
    uglify  = require('gulp-uglify'),
    plumber = require('gulp-plumber'),
    clean   = require('gulp-clean'),
    rename  = require('gulp-rename'),
    package = require('./package.json');

var sharedPath = 'shared/assets/frontend/js/analytics-src/';
var paths = {
  output : 'shared/assets/frontend/js/analytics/',
  scripts : [
    sharedPath + 'analytics.init.js',
    sharedPath + 'analytics.utils.js',
    sharedPath + 'analytics.forms.js',
    sharedPath + 'analytics.events.js',
    sharedPath + 'analytics.storage.js',
    sharedPath + 'analytics.lead-tracking.js',
    sharedPath + 'analytics.page-tracking.js',
    sharedPath + 'analytics.load.js',
  ],
  test: [
    'test/spec/**/*.js'
  ]
};

var banner = [
  '/*! ',
    'Inbound Analytics',
    'v<%= package.version %> | ',
    '(c) ' + new Date().getFullYear() + ' <%= package.author %> |',
    ' <%= package.homepage %>',
  ' */',
  '\n'
].join('');

gulp.task('scripts', ['clean'], function() {
  return gulp.src(paths.scripts)
    .pipe(plumber())
    .pipe(concat('inboundAnalytics.js'))
    .pipe(header(banner, { package : package }))
    .pipe(gulp.dest('shared/assets/frontend/js/analytics/'))
    .pipe(rename({ suffix: '.min' }))
    .pipe(uglify())
    .pipe(header(banner, { package : package }))
    .pipe(gulp.dest('shared/assets/frontend/js/analytics/'));
});

gulp.task('lint', function () {
  return gulp.src(paths.scripts)
    .pipe(plumber())
    .pipe(jshint())
    .pipe(jshint.reporter('jshint-stylish'));
});

gulp.task('clean', function () {
  return gulp.src(paths.output, { read: false })
    .pipe(plumber())
    .pipe(clean());
});

gulp.task('test', function() {
  return gulp.src(paths.scripts.concat(paths.test))
    .pipe(plumber())
    .pipe(karma({ configFile: 'test/karma.conf.js' }))
    .on('error', function(err) { throw err; });
});

/* Watch Files For Changes */
gulp.task('watch', function() {
    //gulp.watch('shared/assets/frontend/js/analytics-src/*.js', ['lint', 'scripts']);
    gulp.watch('shared/assets/frontend/js/analytics-src/*.js', ['default']);
    //gulp.watch('scss/*.scss', ['sass']);
});

gulp.task('copyfonts', function() {
gulp.src('./bower_components/font-awesome/fonts/**/*.{ttf,woff,eof,svg}')
.pipe(gulp.dest('./fonts'));
});

gulp.task('default', [
  'lint',
  'clean',
  'scripts',
  // 'test'
]);
