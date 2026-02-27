# Contributing to User Switching

Bug reports, code contributions, and general feedback are very welcome. These should be submitted through [the GitHub repository](https://github.com/johnbillion/user-switching). Development happens in the `develop` branch, and any pull requests should be made against that branch please.

## Inclusivity and Code of Conduct

Contributions to User Switching are welcome from anyone. Whether you are new to Open Source or a seasoned veteran, all constructive contribution is welcome and I'll endeavour to support you when I can.

This project is released with <a href="CODE_OF_CONDUCT.md">a contributor code of conduct</a> and by participating in this project you agree to abide by its terms. The code of conduct is nothing to worry about, if you are a respectful human being then all will be good.

## AI-assisted development

AI-assisted development is welcome and encouraged, but you must:

- Always disclose your use of AI-assisted coding agents. Failure to do so may result in your contribution being refused.
- Respect the GNU GPL software license that applies to this project.
- Prefer human-written issue descriptions and PR descriptions over AI-generated ones.
- Keep written descriptions brief, there is no need to write a novel that describes every issue or change in detail. Brevity is a skill.

## Setting up Locally

You can clone this repo and activate it like a normal WordPress plugin. If you want to contribute to User Switching, you should install the developer dependencies in order to run the tests.

### Prerequisites

* [Composer](https://getcomposer.org/)
* [Node.js](https://nodejs.org/) (version 18 or later)
* [Docker Desktop](https://www.docker.com/desktop) to run the tests

### Setup

Install the PHP dependencies:

	composer install

Install the Node.js dependencies:

	npm install

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

To run a single test:

	composer test:acceptance -- tests/acceptance/AccessDenied.spec.ts

To stop the Docker containers:

	composer test:stop

## Releasing a New Version

See [RELEASING.md](RELEASING.md).

## Ethical Open Source

User Switching is considered **Ethical Open Source** because it meets all of the criteria of [The Ethical Source Definition (ESD)](https://ethicalsource.dev/definition/):

1. It benefits the commons.
2. It is created in the open.
3. Its community is welcoming and just.
4. It puts accessibility first.
5. It prioritizes user safety.
6. It protects user privacy.
7. It encourages fair compensation.
