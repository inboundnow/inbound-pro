module.exports = function (config) {
  config.set({
    basePath : '',
    // enable / disable watching file and executing tests whenever any file changes
    // CLI --auto-watch --no-auto-watch
    autoWatch : true,
    frameworks: ['jasmine'],
    // Start these browsers, currently available:
    // - Chrome
    // - ChromeCanary
    // - Firefox
    // - Opera
    // - Safari (only Mac)
    // - PhantomJS
    // - IE (only Windows)
    // CLI --browsers Chrome,Firefox,Safari
    browsers : ['Chrome'],
    // list of files / patterns to exclude
    exclude: [],
    plugins : [
      'karma-spec-reporter',
      'karma-chrome-launcher',
      'karma-phantomjs-launcher',
      'karma-jasmine'
    ],
    //singleRun: false,
    reporters : ['spec']
  });
};
