[![Build Status](https://travis-ci.org/johnbillion/user-switching.svg?branch=develop)](https://travis-ci.org/johnbillion/user-switching)

# User Switching Tests

## Prerequisites

* [Composer](https://getcomposer.org/)

## Setup

1. Install the PHP dependencies:

       composer install

2. Check the MySQL database credentials in the `tests/.env.example` file. If your database details differ, copy this file to `tests/.env` and amend them as necessary.

**Important:** Ensure you use a separate test database (eg. `wordpress_test`) because, just like the WordPress test suite, the database will be wiped clean with every test run.

## Running the Tests

To run the whole test suite which includes PHPUnit unit tests, PHPCS code sniffs, and WordHat functional tests:

	composer test

To run just the unit tests:

	composer test:ut

To run just the code sniffs:

	composer test:cs

To run just the functional tests:

    composer test:ft
