#!/usr/bin/env bash

function version {
	echo "$@" | gawk -F. '{ printf("%03d%03d%03d\n", $1,$2,$3); }';
}

# Lint all the PHP files for syntax errors:
# (This is primarily used to ensure newer PHP syntax isn't accidentally used.)
if find . -not \( -path ./vendor -prune \) -not \( -path ./features -prune \) -name "*.php" -exec php -l {} \; | grep "^[Parse error|Fatal error]"; then
	exit 1;
fi;

# Specify the directory where the WordPress test library lives:
if [ -z "$WP_TESTS_DIR" ]; then
	WP_TESTS_DIR="/tmp/wordpress-tests-lib"
fi

# Nicer error message if the setup script hasn't been run:
if [ ! -d "$WP_TESTS_DIR" ]; then
	echo "Please install the test suite with the following command:"
	echo "./bin/install-wp-tests.sh wordpress_test <db-user> <db-pass> [<db-host>]"
	exit 1
fi

# Nicer error message if the Composer dependencies haven't been installed:
if [ ! -d "vendor" ]; then
	echo "Please install the Composer dependencies with the following command:"
	echo "composer install"
	exit 1
fi

# Run single-site unit tests:
export WP_MULTISITE=0
./vendor/bin/phpunit -v --exclude-group=ms-required

# Run Multisite unit tests:
export WP_MULTISITE=1
./vendor/bin/phpunit -v --exclude-group=ms-excluded

phpv=(`php -v`)
ver=${phpv[1]}
profile=${1:-default}

# Run functional tests on PHP 5.4 or later:
if [ "$(version "$ver")" -gt "$(version "5.4")" ]; then

	php -S localhost:8000 -t vendor/wordpress -d disable_functions=mail &
	./vendor/bin/behat --profile="$profile"
	kill $!

fi
