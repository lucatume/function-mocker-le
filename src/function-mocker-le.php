<?php

namespace tad\FunctionMockerLe;

class Functions {

	/**
	 * @var array Stores the callbacks assigned to each function defined by the
	 *            `define` function in an associative array with the [<function name> => <callback>]
	 *            format.
	 */
	public static $defined = [];

	public static function undefined($function) {
		return function () use ($function) {
			throw new UndefinedFunctionException("Function '{$function}' was created by Function Mocker LE but is now undefined.");
		};
	}
}

class UndefinedFunctionException extends \Exception {

}

/**
 * Defines a non defined function, or redefines one defined by this class, to return the value of a callback.
 *
 * @param string   $function The function to define
 * @param callable $callback A callable closure, class/instance and method couple or function name.
 */
function define($function, $callback) {
	if (!isset(Functions::$defined[$function]) && function_exists($function)) {
		$message = "Function {$function} has been defined before Function Mocker LE did its first redefinition attempt.";
		$message .= "\nIf you need to redefine (monkey patch) an existing function use the Function Mocker library (lucatume/function-mocker).";
		throw new \RunTimeException($message);
	}

	Functions::$defined[$function] = $callback;

	$function       = array_filter(explode('\\', $function));
	$namespaceFrags = array_splice($function, 0, count($function) - 1);
	$namespace      = empty($namespaceFrags) ? '' : 'namespace ' . implode('\\', $namespaceFrags) . ';';
	$function       = reset($function);
	$functionFqn    = empty($namespace) ? $function : trim($namespace, ';') . '\\' . $function;

	if (function_exists($functionFqn)) {
		return;
	}

	$code = <<< PHP
{$namespace}
function {$function}(){
	\$f = \\tad\\FunctionMockerLE\\Functions::\$defined['{$function}'];
	
	return call_user_func_array(\$f, func_get_args());
}
PHP;

	eval($code);
}

/**
 * Defines a group of functions all to return the same callback.
 *
 * @param array          $functions
 * @param       callable $callback
 */
function defineAll(array $functions, $callback) {
	foreach ($functions as $function) {
		define($function, $callback);
	}
}

/**
 * Defines a group of functions using a map.
 *
 * @param array $map The definition map, format [<function> => <callback>]
 */
function defineWithMap(array $map) {
	foreach ($map as $function => $callback) {
		define($function, $callback);
	}
}

/**
 * Defines a group of functions to return simple values with a map.
 *
 * @param array $map The definition map, format [<function> => <value>]
 */
function defineWithValueMap(array $map) {
	foreach ($map as $function => $value) {
		define($function, function () use ($value) {
			return $value;
		});
	}
}

/**
 * Returns a random name for a function.
 *
 * @return string
 */
function randomName() {
	return 'function_' . md5(uniqid('function', true));
}

/**
 * Undefines a function defined by Function Mocker LE.
 *
 * Undefined functions will throw an UndefinedFunctionException when called.
 *
 * @param string $function
 */
function undefine($function) {
	Functions::$defined[$function] = Functions::undefined($function);
}

/**
 * Bulk undefines all functions defined by Function Mocker LE or a group of functions.
 *
 * Undefined functions will throw an UndefinedFunctionException when called.
 *
 * @param array|null $functions
 */
function undefineAll(array $functions = null) {
	if (null === $functions) {
		undefineAll(array_keys(Functions::$defined));

		return;
	}

	foreach ($functions as $function) {
		undefine($function);
	}
}
