module.exports = function(grunt) {
    'use strict';

	require('load-grunt-tasks')(grunt);

    var pkg = grunt.file.readJSON('package.json');
	var gig = require('gitignore-globs');
	var gag = require('gitattributes-globs');
	var ignored_gitignore = gig('.gitignore', { negate: true } ).map(function(value) {
		return value.replace(/^!\//,'!');
	});
    var ignored_gitattributes = gag( '.gitattributes', { negate: true } ).map(function(value) {
		return value.replace(/^!\//,'!');
    });
	var config = {};

    config.pkg = pkg;

	config['convert-svg-to-png'] = {
		normal: {
			options: {
				size: {
					w: '128px',
					h: '128px'
				}
			},
			files: [
				{
					expand: true,
					src: [
						'assets-wp-repo/icon.svg'
					],
					dest: 'assets-wp-repo/128'
				}
			]
		},
		retina: {
			options: {
				size: {
					w: '256px',
					h: '256px'
				}
			},
			files: [
				{
					src: [
						'assets-wp-repo/icon.svg'
					],
					dest: 'assets-wp-repo/256'
				}
			]
		}
	};

	config.clean = {
		main: [
			'<%= wp_deploy.deploy.options.build_dir %>'
		],
		icons: Object.keys(config['convert-svg-to-png']).map(function(key){
			return config['convert-svg-to-png'][ key ].files[0].dest;
		})
	};

	config.copy = {
		main: {
			src: [
				'**',
				'!.*',
				'!.git/**',
				'!<%= wp_deploy.deploy.options.assets_dir %>/**',
				'!<%= wp_deploy.deploy.options.build_dir %>/**',
				ignored_gitignore,
				ignored_gitattributes
			],
			dest: '<%= wp_deploy.deploy.options.build_dir %>/'
		}
	};

	config.gitstatus = {
		require_clean: {
			options: {
				callback: function( result ) {
					result.forEach(function(status){
						if ( 'M' === status.code[1] ) {
							grunt.fail.fatal('Git working directory not clean.');
						}
					});
				}
			}
		}
	};

	config.rename = {
		icons:{
			expand: true,
			src: [
				'assets-wp-repo/*/icon.png'
			],
			rename: function (dest,src) {
				return src.replace(/assets-wp-repo\/(\d+)\/icon.png/,'assets-wp-repo/icon-$1x$1.png');
			}
		}
	};

	config.version = {
		main: {
			options: {
				prefix: 'Version:[\\s]+'
			},
			src: [
				'<%= pkg.name %>.php'
			]
		},
		readme: {
			options: {
				prefix: 'Stable tag:[\\s]+'
			},
			src: [
				'readme.md'
			]
		},
		pkg: {
			src: [
				'package.json'
			]
		}
	};

	config.wp_deploy = {
		deploy: {
			options: {
				deploy_trunk: true,
				deploy_tag: true,
				plugin_slug: '<%= pkg.name %>',
				build_dir: 'build',
				assets_dir: 'assets-wp-repo'
			}
		},
		assets: {
			options: {
				deploy_trunk: false,
				deploy_tag: false,
				plugin_slug: '<%= pkg.name %>',
				build_dir: '<%= wp_deploy.deploy.options.build_dir %>',
				assets_dir: '<%= wp_deploy.deploy.options.assets_dir %>'
			}
		},
		ci: {
			options: {
				skip_confirmation: true,
				force_interactive: false,
				deploy_trunk: true,
				deploy_tag: true,
				svn_user: 'johnbillion',
				plugin_slug: '<%= pkg.name %>',
				build_dir: '<%= wp_deploy.deploy.options.build_dir %>',
				assets_dir: '<%= wp_deploy.deploy.options.assets_dir %>'
			}
		}
	};

	config.gitcommit = {
		version: {
			options: {
				message: 'Version <%= grunt.file.readJSON("package.json").version %>'
			},
			files: {
				src: [
					Object.keys(config.version).map(function(key){
						return config.version[ key ].src;
					})
				]
			}
		}
	};

	config.gittag = {
		version: {
			options: {
				tag: '<%= grunt.file.readJSON("package.json").version %>'
			}
		}
	};

	grunt.initConfig(config);

	grunt.registerTask('bump', function(version) {
		if ( ! version ) {
			grunt.fail.fatal( 'No version specified. Usage: bump:major, bump:minor, bump:patch, bump:x.y.z' );
		}

		grunt.task.run([
			'gitstatus:require_clean',
			'version::' + version,
			'gitcommit:version',
			'gittag:version'
		]);
	});

	grunt.registerTask('icons', [
		'convert-svg-to-png',
		'rename:icons',
		'clean:icons'
	]);

	grunt.registerTask('build', [
		'clean',
		'copy'
	]);

	grunt.registerTask('deploy', [
		'build',
		'wp_deploy'
	]);

	grunt.registerTask('deploy:assets', [
		'build',
		'wp_deploy:assets'
	]);

	grunt.registerTask('deploy:ci', [
		'build',
		'wp_deploy:ci'
	]);

};
