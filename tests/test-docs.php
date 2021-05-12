<?php

declare(strict_types = 1);

class TestDocumentation extends \Johnbillion\DocsStandards\TestCase {

	protected function getTestFunctions() : array {
		return array(
			'user_switching_set_olduser_cookie',
			'user_switching_clear_olduser_cookie',
			'user_switching_get_olduser_cookie',
			'user_switching_get_auth_cookie',
			'switch_to_user',
			'switch_off_user',
			'current_user_switched',
		);
	}

	protected function getTestClasses() : array {
		return array(
			'user_switching',
		);
	}

}
