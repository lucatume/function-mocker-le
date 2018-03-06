<?php

namespace tad\FunctionMockerLe;

/**
 * Interface Environment
 *
 * The interface representing a environment to be setup.
 *
 * @package tad\FunctionMockerLe
 */
interface Environment {

  /**
   * Returns the environment slug.
   *
   * @return string
   */
  public function name();

  /**
   * Returns a list of the functions defined by the Environment.
   *
   * @return array
   */
  public function defined();

  /**
   * Defines, using function-mocker-le API, the functions to define.
   *
   * @param null $arg1 One or more additional parameters passed by the `setupEnvironment` function
   *
   * @return void
   *
   * @see \tad\FunctionMockerLe\setupEnvironment()
   */
  public function setUp(...$args);

  /**
   * Tears down the definitions made by the environment in the setup phase.
   *
   * @return void
   */
  public function tearDown();
}

