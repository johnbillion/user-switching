module.exports = function(grunt) {
    'use strict';

    var pkg = grunt.file.readJSON('package.json');
	var parse = require('gitignore-globs');
	var ignored_gitignore = parse('.gitignore', { negate: true } ).map(function(value) {
		return value.replace(/^!\//,'!');
	});

    require('load-grunt-tasks')(grunt);

    grunt.initConfig({
        pkg: pkg,

		clean: {
			main: [
				'<%= wp_deploy.deploy.options.build_dir %>'
			]
        },

		copy: {
			main: {
				src: [
					'**',
					'!.*',
					'!.git/**',
					'!<%= wp_deploy.deploy.options.assets_dir %>/**',
					'!<%= wp_deploy.deploy.options.build_dir %>/**',
					'!CONTRIBUTING.md',
					'!Gruntfile.js',
					'!readme.md',
					'!behat.yml',
					'!bin/**',
					'!features/**',
					'!package.json',
					'!phpcs.xml.dist',
					'!phpunit.xml.dist',
					'!tests/**',
					ignored_gitignore
				],
				dest: '<%= wp_deploy.deploy.options.build_dir %>/'
			}
        },

		wp_deploy: {
			deploy: {
				options: {
					plugin_slug: '<%= pkg.name %>',
					build_dir: 'build',
					assets_dir: 'assets-wp-repo'
				}
			}
        },

        wp_readme_to_markdown: {
            convert: {
                files: {
                    'readme.md': 'readme.txt'
                },
                options: {
                    'screenshot_url': 'assets-wp-repo/{screenshot}.png',
                    'post_convert': function( readme ) {
                        // Banner
                        if ( grunt.file.exists( 'assets-wp-repo/banner-1544x500.png' ) ) {
                            readme = readme.replace( '**Contributors:**', '![Banner Image](assets-wp-repo/banner-1544x500.png)\r\n\r\n**Contributors:**' );
                        }

                        // Badges
                        readme = grunt.template.process( pkg.readme_badges.join( '\n' ) ) + '\n\n' + readme;

                        return readme;
                    }
                }
            }
        }
    });
};
