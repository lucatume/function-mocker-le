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
   * Returns a list of the functions defined by the System.
   *
   * @return array
   */
  public function defined();

  /**
   * Defines, using function-mocker-le API, the functions to define.
   *
   * @param null $arg1 One or more additional parameters passed by the `setupSystem` function
   *
   * @return void
   *
   * @see \tad\FunctionMockerLe\setupSystem()
   */
  public function setUp(...$args);

  /**
   * Tears down the definitions made by the system in the setup phase.
   *
   * @return void
   */
  public function tearDown();
}

