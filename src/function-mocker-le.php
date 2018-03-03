<?php

namespace tad\FunctionMockerLe;

/**
 * Defines a non defined function, or redefines one defined by this class, to
 * return the value of a callback.
 *
 * @param string   $function The function to define
 * @param callable $callback A callable closure, class/instance and method
 *                           couple or function name.
 */
function define( $function, $callback ) {
	Store::$defined[ $function ] = $callback;

	$function       = array_filter( explode( '\\', $function ) );
	$namespaceFrags = array_splice( $function, 0, count( $function ) - 1 );
	$namespace      = empty( $namespaceFrags ) ? '' : 'namespace ' . implode( '\\', $namespaceFrags ) . ';';
	$function       = reset( $function );
	$functionFqn    = empty( $namespace ) ? $function : trim( $namespace, ';' ) . '\\' . $function;

	if ( function_exists( $functionFqn ) ) {
		return;
	}

	$code = <<< PHP
{$namespace}
function {$function}(){
	\$f = \\tad\\FunctionMockerLE\\Store::\$defined['{$function}'];
	
	return call_user_func_array(\$f, func_get_args());
}
PHP;

	eval( $code );
}

/**
 * Defines a group of functions all to return the same callback.
 *
 * @param array          $functions
 * @param       callable $callback
 */
function defineAll( array $functions, $callback ) {
	foreach ( $functions as $function ) {
		define( $function, $callback );
	}
}

/**
 * Defines a group of functions using a map.
 *
 * @param array $map The definition map, format [<function> => <callback>]
 */
function defineWithMap( array $map ) {
	foreach ( $map as $function => $callback ) {
		define( $function, $callback );
	}
}

/**
 * Defines a group of functions to return simple values with a map.
 *
 * @param array $map The definition map, format [<function> => <value>]
 */
function defineWithValueMap( array $map ) {
	foreach ( $map as $function => $value ) {
		define( $function, function () use ( $value ) {
			return $value;
		} );
	}
}

/**
 * Returns a random name for a function.
 *
 * @return string
 */
function randomName() {
	return 'function_' . md5( uniqid( 'function', true ) );
}

/**
 * Undefines a function defined by Function Mocker LE.
 *
 * Undefined functions will throw an UndefinedFunctionException when called.
 *
 * @param string $function
 */
function undefine( $function ) {
	Store::$defined[ $function ] = Store::undefined( $function );
}

/**
 * Bulk undefines all functions defined by Function Mocker LE or a group of
 * functions.
 *
 * Undefined functions will throw an UndefinedFunctionException when called;
 * the method will tear down all the systems too.
 *
 * @param array|null $functions
 */
function undefineAll( array $functions = null ) {
	foreach ( Store::$systems as $name => $system ) {
		$system->tearDown();
	}

	Store::$systems = [];

	if ( null === $functions ) {
		undefineAll( array_keys( Store::$defined ) );

		return;
	}

	foreach ( $functions as $function ) {
		undefine( $function );
	}
}

/**
 * Sets up a system calling its `setUp` method.
 *
 * @param \tad\FunctionMockerLe\System $system
 * @param mixed|null                   $arg1       One or more additional
 *                                                 arguments that will be
 *                                                 passed to the system.
 */
function setupSystem( System $system, $arg1 = null ) {
	$args                           = func_get_args();
	$sys                            = array_shift( $args );
	Store::$systems[ $sys->name() ] = $sys;


	if ( count( $args ) === 0 ) {
		$sys->setUp();
	} else {
		call_user_func_array( [ $sys, 'setUp' ], $args );
	}
}

/**
 * Calls a specific system `tearDown` method.
 *
 * @param string $systemName The name of a system set up using hte
 *                           `setupSystem` function.
 */
function tearDownSystem( $systemName ) {
	if ( ! array_key_exists( $systemName, Store::$systems ) ) {
		throw new \InvalidArgumentException( "No system {$systemName} was ever set up." );
	}

	Store::$systems[ $systemName ]->tearDown();
}

/**
 * Calls the replacement function with the specified arguments.
 *
 * @param       string $function The function name
 * @param array        $args     An array of arguments to call the function with
 *
 * @return mixed|null
 */
function callback( $function, array $args = [] ) {
	$function = ltrim( $function, '\\' );

	if ( ! isset( Store::$defined[ $function ] ) ) {
		return null;
	}

	return call_user_func_array( Store::$defined[ $function ], $args);
}
