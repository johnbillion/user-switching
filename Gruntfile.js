module.exports = function(grunt) {
    'use strict';

    var pkg = grunt.file.readJSON('package.json');

    require('load-grunt-tasks')(grunt);

    grunt.initConfig({
        pkg: pkg,

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
