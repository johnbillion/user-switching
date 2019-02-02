#!/usr/bin/env bash

set -e

# Specify the directory where the WordPress test library lives:
TMPDIR=${TMPDIR-/tmp}
if [ -z "$WP_TESTS_DIR" ]; then
	WP_TESTS_DIR="${TMPDIR}/wordpress-tests-lib"
fi

# Specify the directory where the WordPress installation lives:
if [ -z "$WP_CORE_DIR" ]; then
	WP_CORE_DIR="${TMPDIR}/wordpress"
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

# Run the code sniffer:
./vendor/bin/phpcs -p -s --colors user-switching.php

# Run functional tests:
php -S localhost:8000 -t "$WP_CORE_DIR" -d disable_functions=mail &

./vendor/bin/wp db reset --yes --path="$WP_CORE_DIR"
./vendor/bin/wp core install --path="$WP_CORE_DIR" --url='http://localhost:8000' \
	--title="Example" --admin_user="admin" --admin_password="admin" --admin_email="admin@example.com"
BEHAT_PARAMS='{"extensions" : {"PaulGibbs\\WordpressBehatExtension" : {"path" : "'$WP_CORE_DIR'"}}}' \
	./vendor/bin/behat

kill $!
