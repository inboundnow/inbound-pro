/* for main build processes we use gulp see gulpfile.js */
module.exports = function(grunt) {

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    wp_readme_to_markdown: {
      leads: {
          files: {
            'readme.md': 'readme.txt'
          },
      },
    },
  });

  grunt.loadNpmTasks('grunt-wp-readme-to-markdown');

  grunt.registerTask('default', ['wp_readme_to_markdown']);

};