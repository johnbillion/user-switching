<?php

abstract class User_Switching_Test extends WP_UnitTestCase {

	function setUp() {

		$this->admin = $this->factory->user->create_and_get( array(
			'role' => 'administrator'
		) );
		$this->editor = $this->factory->user->create_and_get( array(
			'role' => 'editor'
		) );
		$this->author = $this->factory->user->create_and_get( array(
			'role' => 'author'
		) );
		$this->contributor = $this->factory->user->create_and_get( array(
			'role' => 'contributor'
		) );
		$this->subscriber = $this->factory->user->create_and_get( array(
			'role' => 'subscriber'
		) );
		$this->no_role = $this->factory->user->create_and_get( array(
			'role' => 'administrator'
		) );
		$this->no_role->remove_role( 'administrator' );

		if ( is_multisite() ) {
			$this->super = $this->factory->user->create_and_get( array(
				'role' => 'administrator'
			) );
			grant_super_admin( $this->super->ID );
		}

		parent::setUp();

	}

}
