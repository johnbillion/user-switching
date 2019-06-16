[![Build Status](https://travis-ci.org/johnbillion/user-switching.svg?branch=develop)](https://travis-ci.org/johnbillion/user-switching)

# Contributing to User Switching

Code contributions and bug reports are very welcome. These should be submitted through [the GitHub repository](https://github.com/johnbillion/user-switching). Development happens in the `develop` branch, and any pull requests should be made against that branch please.

* [Reporting Security Issues](#reporting-security-issues)
* [Setting up Locally](#setting-up-locally)
* [Running the Tests](#running-the-tests)

## Reporting Security Issues

If you discover a security issue in User Switching, please report it to [the security program on HackerOne](https://hackerone.com/johnblackbourn). Do not report security issues on GitHub or the WordPress.org support forums. Thank you.

## Setting up Locally

You can clone this repo and activate it like a normal WordPress plugin. If you want to contribute to User Switching, you should install the developer dependencies in order to run the tests.

### Prerequisites

* [Composer](https://getcomposer.org/)
* [Node](https://nodejs.org/)

### Setup

1. Install the PHP dependencies:

       composer install

2. Install the Node dependencies:

       npm install

3. Check the MySQL database credentials in the `tests/.env.example` file. If your database details differ, copy this file to `tests/.env` and amend them as necessary.

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
