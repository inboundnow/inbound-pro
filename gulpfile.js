/**
 *  Tasks
 *  gulp sync-gpl [Syncs Landing Pages, Leads, Calls to Action plugin to plugin directory.]
 *  gulp watch [ Listens for changes to /core/shared/asset/frontend/analytics-src/ and compiles output to minified and non-minified version ]
 *  gulp translate [ Generates translations.zip file from available .mo files in ../translations/lang/mo/ folder
 */
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
    zip = require('gulp-zip'),
    //phplint = require('phplint').lint,
    package = require('./package.json');

/**
 *  Setup Inbound Analytics JS Build objects
 */
var sharedPath = 'core/shared/assets/js/frontend/analytics-src/';
var paths = {
    output: 'core/shared/assets/js/frontend/analytics/',
    scripts: [
        sharedPath + 'analytics.init.js',
        sharedPath + 'analytics.hooks.js',
        sharedPath + 'analytics.utils.js',
        sharedPath + 'analytics.forms.js',
        sharedPath + 'analytics.events.js',
        sharedPath + 'analytics.storage.js',
        sharedPath + 'analytics.lead.js',
        sharedPath + 'analytics.page.js',
        sharedPath + 'analytics.start.js',
    ],
    test: [
        'tests/spec/**/*.js'
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


/**
 *   Watch for changes to Inbound Analytics JS Assets 
 */
gulp.task('watch', function() {
    gulp.watch('core/shared/assets/js/frontend/analytics-src/*.js', ['compilejs']);
});

/**
 *  Sync Landing Pages
 */
gulp.task('sync-lp', function () {
     gulp.src("../landing-pages/**").pipe(clean({force:true}));
     gulp.src(['./core/landing-pages/**']).pipe(clean()).pipe(gulp.dest('../landing-pages/'));
     gulp.src(['./core/shared/**']).pipe(clean()).pipe(gulp.dest('../landing-pages/shared/'));
});

/**
 *  Sync Landing Pages
 */
gulp.task('sync-cta', function () {
     gulp.src("../cta/**").pipe(clean({force:true}));
     gulp.src(['./core/cta/**']).pipe(gulp.dest('../cta/'));
     gulp.src(['./core/shared/**']).pipe(gulp.dest('../cta/shared/'));
});

/**
 *  Sync Landing Pages
 */
gulp.task('sync-leads', function () {
    gulp.src("../leads/**").pipe(clean({force:true}));
    gulp.src(['./core/leads/**']).pipe(gulp.dest('../leads/'));
    gulp.src(['./core/shared/**']).pipe(gulp.dest('../leads/shared/'));
});

/**
 *  Sync GPL
 */
gulp.task('sync-gpl' , ['sync-lp','sync-cta','sync-leads'] , function() {
     //do more
});

function getPath(path){

    var removeFiles = ['./core/'+path+'/node_modules/',
                        './core/'+path+'/tests/',
                        './core/'+path+'/shared/',
                        './core/'+path+'/*.jpg',
                        './core/'+path+'/*.js',
                        './core/'+path+'/*.sh',
                        './core/'+path+'/*.json',
                        './core/'+path+'/*.ini',
                        './core/'+path+'/*.png',
                        './core/'+path+'/*.travis.yml',
                        './core/'+path+'/*.dist',
                        './core/'+path+'/*.md',
                        './core/'+path+'/*.txt'];
   return removeFiles;
}


/**
 *  Tasks for compiling InboundAnalytics JS assets
 */
gulp.task('scripts', ['clean'], function() {
    return gulp.src(paths.scripts)
        .pipe(plumber())
        .pipe(concat('inboundAnalytics.js'))
        .pipe(header(banner, {
            package: package
        }))
        .pipe(gulp.dest('shared/assets/js/frontend/analytics/'))
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(uglify())
        .pipe(header(banner, {
            package: package
        }))
        .pipe(gulp.dest('shared/assets/js/frontend/analytics/'));
});

gulp.task('lint', function() {
    return gulp.src(paths.scripts)
        .pipe(plumber())
        .pipe(jshint())
        .pipe(jshint.reporter('jshint-stylish'));
});

gulp.task('clean', function() {
    return gulp.src(paths.output, {
            read: false
        })
        .pipe(plumber())
        .pipe(clean());
});

gulp.task("generateDocs", function() {
    gulp.src("core/shared/assets/js/frontend/analytics-src/analytics.events.js")
        .pipe(markdox())
        .pipe(rename({
            extname: ".md"
        }))
        .pipe(gulp.dest("./core/shared/docs"));
});

gulp.task('compilejs', [
    'lint',
    'clean',
    'scripts',
    'generateDocs'
]);


/**
 *  For compiling translation mo files
 */
 

gulp.task('zip-translations', function () {
    return gulp.src('../translations/lang/mo/**.mo')
        .pipe(zip('translations.zip'))
        .pipe(gulp.dest('../translations/'));
});

gulp.task('translate' , ['zip-translations'] , function() {
    
});