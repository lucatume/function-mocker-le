<?php

namespace tad\FunctionMockerLe\Systems;


use tad\FunctionMockerLe\System;

class WordPress implements System {

	/**
	 * Returns the system slug.
	 *
	 * @return string
	 */
	public function name() {
		return 'WordPress';
	}

	/**
	 * Defines, using function-mocker-le API, the functions to define.
	 *
	 * @return void
	 */
	public function setUp() {
		// TODO: Implement setUp() method.
	}

	/**
	 * Tears down the definitions made by the system in the setup phase.
	 *
	 * @return void
	 */
	public function tearDown() {
		// TODO: Implement tearDown() method.
	}
}