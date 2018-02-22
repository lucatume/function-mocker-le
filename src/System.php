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
	 * @return void
	 */
	public function setUp();

	/**
	 * Tears down the definitions made by the system in the setup phase.
	 *
	 * @return void
	 */
	public function tearDown();
}

