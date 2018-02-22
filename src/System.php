<?php

namespace tad\FunctionMockerLe;

/**
 * Interface System
 *
 * The interface representing a system to be setup.
 *
 * @package tad\FunctionMockerLe
 */
interface System {

	/**
	 * Returns the system slug.
	 *
	 * @return string
	 */
	public function name();

	/**
	 * Defines, using function-mocker-le API, the functions to define.
	 *
	 * @param null $arg1 One or more additional parameters passed by the `setupSystem` function
	 *
	 * @return void
	 *
	 * @see \tad\FunctionMockerLe\setupSystem()
	 */
	public function setUp($arg1 = null);

	/**
	 * Tears down the definitions made by the system in the setup phase.
	 *
	 * @return void
	 */
	public function tearDown();
}

