#!/usr/bin/env bash

set -e

# Specify the directory where the WordPress installation lives:
WP_CORE_DIR="${PWD}/tests/wordpress"

# Run functional tests:
php -S localhost:8000 -t "$WP_CORE_DIR" -d disable_functions=mail 2>/dev/null &

./vendor/bin/wp db reset --yes --color --path="$WP_CORE_DIR"
./vendor/bin/wp core install --color --path="$WP_CORE_DIR" --url='http://localhost:8000' \
	--title="Example" --admin_user="admin" --admin_password="admin" --admin_email="admin@example.com"
BEHAT_PARAMS='{"extensions" : {"PaulGibbs\\WordpressBehatExtension" : {"path" : "'$WP_CORE_DIR'"}}}' \
	./vendor/bin/behat --colors

# Stop the PHP web server:
kill $!
