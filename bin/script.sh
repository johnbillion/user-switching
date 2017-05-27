#!/usr/bin/env bash

function version {
	echo "$@" | gawk -F. '{ printf("%03d%03d%03d\n", $1,$2,$3); }';
}

# Lint all the PHP files for syntax errors:
# (This is primarily used to ensure newer PHP syntax isn't accidentally used.)
if find . -not \( -path ./vendor -prune \) -not \( -path ./features -prune \) -name "*.php" -exec php -l {} \; | grep "^[Parse error|Fatal error]"; then
	exit 1;
fi;

phpv=(`php -v`)
ver=${phpv[1]}

if [ "$(version "$ver")" -gt "$(version "5.4")" ]; then

	php -S localhost:8000 -t vendor/wordpress -d disable_functions=mail &
	./vendor/bin/behat --profile=travis

fi
