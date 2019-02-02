module.exports = function(grunt) {
    'use strict';

    var pkg = grunt.file.readJSON('package.json');
	var parse = require('gitignore-globs');
	var ignored_gitignore = parse('.gitignore', { negate: true } ).map(function(value) {
		return value.replace(/^!\//,'!');
	});

    // Parse a .gitattributes file and return its glob patterns as an array.
    function parse_list(file, options) {
        var options = options || {};
        options.negate = options.negate || false;

        var fs = require('fs');
        var content = fs.readFileSync(file).toString();
        var patterns = content.split('\n');

        patterns = patterns.filter(function(pattern) {
            return / export-ignore$/.test( pattern.trim() );
        });

        patterns = patterns.map(function(pattern) {
            return pattern.trim().replace( / export-ignore$/, '' ).trim();
        });

        patterns = parse._prepare(patterns);
        var globs = parse._map(patterns);
        if (options.negate) {
            globs = parse._negate(globs);
        }

        return globs;
    }

    var ignored_gitattributes = parse_list( '.gitattributes', { negate: true } ).map(function(value) {
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
					'!readme.md',
					ignored_gitignore,
					ignored_gitattributes
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
