<?php

namespace tad\FunctionMockerLe;

class Store {

	/**
	 * @var array Stores the callbacks assigned to each function defined by the
	 *            `define` function in an associative array with the [<function name> => <callback>]
	 *            format.
	 */
	public static $defined = [];

	/**
	 * @var \tad\FunctionMockerLe\Environment[] Stores the set up environments in a [<name> => <environment-instance>] array.
	 */
	public static $environments = [];

	/**
	 * Returns a closure function throwing an exception.
	 *
	 * @param string $function
	 *
	 * @return \Closure
	 */
	public static function undefined($function) {
		return function () use ($function) {
			throw new UndefinedFunctionException("Function '{$function}' was created by Function Mocker LE but is now undefined.");
		};
	}
}

