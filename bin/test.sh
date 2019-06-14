#!/usr/bin/env bash

# Specify the directory where the WordPress installation lives:
WP_INSTALL_DIR=$(npm run --silent jq '.extra ."wordpress-install-dir"' composer.json -- -r)
WP_CORE_DIR="${PWD}/${WP_INSTALL_DIR}"

# Specify the URL for the site:
WP_URL="localhost:8000"

# Shorthand:
WP="./vendor/bin/wp --color --path=$WP_CORE_DIR --url=http://$WP_URL"

# Start the PHP server:
php -S "$WP_URL" -t "$WP_CORE_DIR" -d disable_functions=mail 2>/dev/null &

# Reset or install the test database:
$WP db reset --yes

# Install WordPress:
$WP core install --title="Example" --admin_user="admin" --admin_password="admin" --admin_email="admin@example.com"

# Run the functional tests:
BEHAT_PARAMS='{"extensions" : {"PaulGibbs\\WordpressBehatExtension" : {"path" : "'$WP_CORE_DIR'"}}}' \
	./vendor/bin/behat --colors

# Stop the PHP web server:
kill $!
