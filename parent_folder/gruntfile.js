module.exports = function (grunt) {

	// Require all grunt-tasks instead of manually initialize them. 
	require('load-grunt-tasks')(grunt);

	grunt.initConfig({

		pkg: grunt.file.readJSON('package.json'),

		// Use Compass to create CSS
		compass: {
			dist: {
				options: {
					sassDir: "<%= pkg.wp_theme_path %>scss",
					cssDir: "<%= pkg.wp_theme_path %>css",
					environment: 'production',
					outputStyle: 'compressed',
					importPath: "<%= pkg.wp_theme_path %>bower_components/foundation/scss"
				}
			}
		},

		// Watch files for change
		watch: {
			styles: {
				files: ["<%= pkg.wp_theme_path %>scss/*.scss"],
				tasks: ["compass"],
				options: {
					livereload: true
				}
			}
		}

	});

	grunt.registerTask('default', ['compass']);

};
