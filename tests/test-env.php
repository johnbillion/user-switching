<?php

class Test_Environment extends WP_UnitTestCase {

	public function test_travis_php_version() {

		$php = getenv( 'TRAVIS_PHP_VERSION' );

		if ( empty( $php ) ) {
			$this->markTestSkipped( 'Not running on Travis' );
		}

		if ( 'hhvm' === $php ) {
			$this->assertTrue( defined( 'HHVM_VERSION' ) );
		} else {
			$this->assertFalse( defined( 'HHVM_VERSION' ) );
		}

	}

}
