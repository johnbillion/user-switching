[![Build Status](https://img.shields.io/github/workflow/status/johnbillion/user-switching/Test/develop?style=flat-square)](https://github.com/johnbillion/user-switching/actions)
[![](https://img.shields.io/badge/contributor-code%20of%20conduct-5e0d73.svg?style=flat-square)](https://github.com/johnbillion/user-switching/blob/develop/CODE_OF_CONDUCT.md)
[![](https://img.shields.io/badge/ethical-open%20source-4baaaa.svg?style=flat-square)](#ethical-open-source)

# Contributing to User Switching

Bug reports, code contributions, and general feedback are very welcome. These should be submitted through [the GitHub repository](https://github.com/johnbillion/user-switching). Development happens in the `develop` branch, and any pull requests should be made against that branch please.

* [Reviews on WordPress.org](#reviews-on-wordpressorg)
* [Reporting Security Issues](#reporting-security-issues)
* [Inclusivity and Code of Conduct](#inclusivity-and-code-of-conduct)
* [Setting up Locally](#setting-up-locally)
* [Running the Tests](#running-the-tests)
* [Releasing a New Version](#releasing-a-new-version)
* [Ethical Open Source](#ethical-open-source)

## Reviews on WordPress.org

If you enjoy using User Switching I would greatly appreciate it <a href="https://wordpress.org/support/plugin/user-switching/reviews/">if you left a positive review on the WordPress.org Plugin Directory</a>. This is the fastest and easiest way to contribute to User Switching 😄.

## Reporting Security Issues

If you discover a security issue in User Switching, please report it to [the security program on HackerOne](https://hackerone.com/johnblackbourn). Do not report security issues on GitHub or the WordPress.org support forums. Thank you.

## Inclusivity and Code of Conduct

Contributions to User Switching are welcome from anyone. Whether you are new to Open Source or a seasoned veteran, all constructive contribution is welcome and I'll endeavour to support you when I can.

This project is released with <a href="https://github.com/johnbillion/user-switching/blob/develop/CODE_OF_CONDUCT.md">a contributor code of conduct</a> and by participating in this project you agree to abide by its terms. The code of conduct is nothing to worry about, if you are a respectful human being then all will be good.

## Setting up Locally

You can clone this repo and activate it like a normal WordPress plugin. If you want to contribute to User Switching, you should install the developer dependencies in order to run the tests.

### Prerequisites

* [Composer](https://getcomposer.org/)
* [Node](https://nodejs.org/)
* A local web server running WordPress

### Setup

1. Install the PHP dependencies:

       composer install

2. Install the Node dependencies:

       npm install

3. Check the MySQL database credentials in the `tests/.env` file and amend them as necessary.

**Important:** Ensure you use a separate test database (eg. `wordpress_test`) because, just like the WordPress test suite, the database will be wiped clean with every test run.

## Running the Tests

To run the whole test suite which includes PHPUnit unit tests, PHPCS code sniffs, PHPStan static analysis, and WordHat functional tests:

	composer test

To run just the unit tests:

	composer test:ut

To run just the code sniffs:

	composer test:cs

To run just the static analysis:

	composer test:phpstan

To run just the functional tests:

	composer test:ft

## Releasing a New Version

User Switching gets automatically deployed to the WordPress.org Plugin Directory whenever a new release is published on GitHub.

Assets such as screenshots and banners are stored in the `.wordpress-org` directory. These get deployed as part of the automated release process too.

In order to deploy only changes to assets, push the change to the `deploy` branch and they will be deployed if they're the only changes in the branch since the last release. This allows for the "Tested up to" value to be bumped as well as assets to be updated in between releases.

## Ethical Open Source

User Switching is considered **Ethical Open Source** because it meets all of the criteria of [The Ethical Source Definition (ESD)](https://ethicalsource.dev/definition/):

1. It benefits the commons.
2. It is created in the open.
3. Its community is welcoming and just.
4. It puts accessibility first.
5. It prioritizes user safety.
6. It protects user privacy.
7. It encourages fair compensation.
