#!/usr/bin/env bash

# -e          Exit immediately if a pipeline returns a non-zero status
# -o pipefail Produce a failure return code if any command errors
set -eo pipefail

# Specify the directory where the WordPress installation lives:
WP_CORE_DIR="${PWD}/tests/wordpress"

# Specify the URL for the site:
WP_URL="localhost:8000"

# Start the PHP server:
php -S "$WP_URL" -t "$WP_CORE_DIR" -d disable_functions=mail 2>/dev/null &
PHP_SERVER_PROCESS_ID=$!

# Run the acceptance tests:
TEST_SITE_WP_DIR=$WP_CORE_DIR \
TEST_SITE_WP_URL="http://$WP_URL" \
	./vendor/bin/codecept run acceptance --steps "$1" \
	|| ( TESTS_EXIT_CODE=$? && kill $PHP_SERVER_PROCESS_ID && exit $TESTS_EXIT_CODE )

# Stop the PHP web server:
kill $PHP_SERVER_PROCESS_ID
