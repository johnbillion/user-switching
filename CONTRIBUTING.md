[![](https://img.shields.io/badge/contributor-code%20of%20conduct-5e0d73.svg?style=flat-square)](https://github.com/johnbillion/user-switching/blob/develop/CODE_OF_CONDUCT.md)
[![](https://img.shields.io/badge/ethical-open%20source-4baaaa.svg?style=flat-square)](#ethical-open-source)

# Contributing to User Switching

Bug reports, code contributions, and general feedback are very welcome. These should be submitted through [the GitHub repository](https://github.com/johnbillion/user-switching). Development happens in the `develop` branch, and any pull requests should be made against that branch please.

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
* [Docker Desktop](https://www.docker.com/desktop) to run the tests

### Setup

Install the PHP dependencies:

	composer install

## Running the Tests

The test suite includes integration and acceptance tests which run in a Docker container. Ensure Docker Desktop is running, then start the containers with:

	composer test:start

To run the whole test suite which includes integration tests, acceptance tests, linting, and static analysis:

	composer test

To run tests individually, run one of:

	composer test:integration
	composer test:acceptance
	composer test:phpcs
	composer test:phpstan

To stop the Docker containers:

	composer test:stop

## Releasing a New Version

These are the steps to take to release a new version of User Switching (for contributors who have push access to the GitHub repo).

### Prior to Release

1. Check [the milestone on GitHub](https://github.com/johnbillion/user-switching/milestones) for open issues or PRs. Fix or reassign as necessary.
1. If this is a non-patch release, check issues and PRs assigned to the patch or minor milestones that will get skipped. Reassign as necessary.
1. Ensure you're on the `develop` branch and all the changes for this release have been merged in.
1. Ensure `readme.md` contains up to date descriptions, "Tested up to" versions, FAQs, screenshots, etc.
1. Ensure `.gitattributes` is up to date with all files that shouldn't be part of the build.
   - To do this, run `git archive --output=user-switching.zip HEAD` then check the contents for files that shouldn't be part of the package.
1. Run `composer test` and ensure everything passes.
1. Run `git push origin develop` (if necessary) and ensure CI is passing.
1. Prepare a changelog for [the Releases page on GitHub](https://github.com/johnbillion/user-switching/releases).

### For Release

1. Bump the plugin version number:
   - `npm run bump:patch` for a patch release (1.2.3 => 1.2.4)
   - `npm run bump:minor` for a minor release (1.2.3 => 1.3.0)
   - `npm run bump:major` for a major release (1.2.3 => 2.0.0)
1. `git push origin develop`
1. `git tag x.y.z`
1. `git push origin --tags`
1. Enter the changelog into [the release on GitHub](https://github.com/johnbillion/user-switching/releases) and publish it.

### Post Release

Publishing a release on GitHub triggers an action which deploys the release to the WordPress.org Plugin Directory. No need to touch Subversion.

New milestones are automatically created for the next major, minor, and patch releases where appropriate.

1. Close the milestone.
1. If this is a non-patch release, manually delete any [unused patch and minor milestones on GitHub](https://github.com/johnbillion/user-switching/milestones).
1. Check the new version has appeared [on the WordPress.org plugin page](https://wordpress.org/plugins/user-switching/) (it'll take a few minutes).
1. Resolve relevant threads on [the plugin's support forums](https://wordpress.org/support/plugin/user-switching/).

### Asset Updates

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
