/**
 *  Tasks
 *  gulp sync-gpl [Syncs Landing Pages, Leads, Calls to Action plugin to plugin directory.]
 *  gulp watch [ Listens for changes to /core/shared/asset/frontend/analytics-src/ and compiles output to minified and non-minified version ]
 *  gulp translate [ Generates translations.zip file from available .mo files in ../translations/lang/mo/ folder
 */
var gulp = require('gulp'),
    jshint = require('gulp-jshint'),
    fs = require('fs'),
    del = require('del'),
    stylish = require('jshint-stylish'),
    header = require('gulp-header'),
    run = require('gulp-run'),
    concat = require('gulp-concat'),
    uglify = require('gulp-uglify'),
    plumber = require('gulp-plumber'),
    clean = require('gulp-clean'),
    rename = require('gulp-rename'),
    copy = require('gulp-copy'),
    markdox = require("gulp-markdox"),
    filter = require("gulp-filter"),
    gulpIgnore = require('gulp-ignore'),
    zip = require('gulp-zip'),
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
        sharedPath + 'analytics.start.js'
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
 * Sync shared folder
 */


gulp.task('shared-lp' , function() {
    return gulp.src(['./core/shared/**']).pipe(gulp.dest('../landing-pages/shared/'));
});

gulp.task('shared-cta' , function() {
    return gulp.src(['./core/shared/**']).pipe(gulp.dest('../cta/shared/'));
});

gulp.task('shared-leads' , function() {
    return gulp.src(['./core/shared/**']).pipe(gulp.dest('../leads/shared/'));
});

gulp.task('shared' ,gulp.series('shared-lp','shared-cta','shared-leads'),  function() {

});

/**
 *  Sync Landing Pages
 */
gulp.task('clean-lp' , function() {
    var dir = "../lp";

    if (!fs.existsSync(dir)){
        fs.mkdirSync(dir);
    }
    return del([
        '../landing-pages/**/*',
        '!../landing-pages/svn{,/**}'
    ], {force: true});
});

gulp.task('copy-lp' , function() {
    return gulp.src(['./core/landing-pages/**' , '!./core/landing-pages/svn/**']).pipe(gulp.dest('../landing-pages/'));
});

gulp.task('lp',gulp.series('clean-lp', 'copy-lp' ,  'shared-lp' ));

/**
 *  Sync Calls to action
 */
gulp.task('clean-cta' , function() {
    var dir = "../cta";

    if (!fs.existsSync(dir)){
        fs.mkdirSync(dir);
    }
    return del([
        '../cta/**/*',
        '!../cta/svn{,/**}'
    ], {force: true});
});

gulp.task('copy-cta' , function() {
    return gulp.src(['./core/cta/**' , '!./core/cta/svn/**']).pipe(gulp.dest('../cta/'));
});

gulp.task('cta',gulp.series('clean-cta', 'copy-cta' , 'shared-cta'));


/**
 *  Sync Leads
 */
gulp.task('clean-leads' , function() {
    var dir = "../leads";

    if (!fs.existsSync(dir)){
        fs.mkdirSync(dir);
    }
    return del([
        '../leads/**/*',
        '!../leads/svn{,/**}'
    ], {force: true});

});

gulp.task('copy-leads' , function() {
    return gulp.src(['./core/leads/**' , '!./core/leads/svn/**']).pipe(gulp.dest('../leads/'));
});

gulp.task('leads',gulp.series('clean-leads', 'copy-leads' , 'shared-leads'));

/**
 *  Sync GPL
 */
gulp.task('gpl' , gulp.series('lp','cta','leads') , function() {
     return console.log('GPL Synced');
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


gulp.task('clean-inboundAnalytics', function() {
    return gulp.src(paths.output, {
            read: false
        })
        .pipe(plumber())
        .pipe(clean());
});

gulp.task('compile-inboundAnalytics',  function() {
    return gulp.src(paths.scripts)
        .pipe(plumber())
        .pipe(concat('inboundAnalytics.js'))
        .pipe(header(banner, {
            package: package
        }))
        .pipe(gulp.dest('core/shared/assets/js/frontend/analytics/'))
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(uglify())
        .pipe(header(banner, {
            package: package
        }))
        .pipe(gulp.dest('./core/shared/assets/js/frontend/analytics/'));
});

gulp.task('lint-inboundAnalytics', function() {
    return gulp.src(paths.scripts)
        .pipe(plumber())
        .pipe(jshint())
        .pipe(jshint.reporter('jshint-stylish'));
});


gulp.task("generateDocs-inboundAnalytics", function() {
    gulp.src("./core/shared/assets/js/frontend/analytics-src/analytics.events.js")
        .pipe(markdox())
        .pipe(rename({
            extname: ".md"
        }))
        .pipe(gulp.dest("./core/shared/docs"));
});

gulp.task('inboundAnalytics', gulp.series(
    'lint-inboundAnalytics',
    'clean-inboundAnalytics',
    'compile-inboundAnalytics'//,
    //'generateDocs-inboundAnalytics'
));


/**
 *   Watch for changes to Inbound Analytics JS Assets
 */
gulp.task('watch', function() {
    gulp.watch('core/shared/assets/js/frontend/analytics-src/*.js', gulp.series('inboundAnalytics'));
});

/**
 *  For compiling translation mo files
 */
gulp.task('zip-translations', function () {
    return gulp.src('../translations/lang/mo/**.mo')
        .pipe(zip('translations.zip'))
        .pipe(gulp.dest('../translations/'));
});

gulp.task('translate' , gulp.series('zip-translations') , function() {
    
});

/**
 * Deploy GPL plugins to SVN
 */
gulp.task('deploy-leads' , function() {
    //return run('sh ./../leads/deploy.sh').exec().pipe(gulp.dest('output'));
});