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
function define($function, $callback) {
    $functionFqn = '\\' . ltrim($function, '\\');
    Store::$defined[$functionFqn] = $callback;
    $function = array_filter(explode('\\', $functionFqn));
    $namespaceFrags = array_splice($function, 0, count($function) - 1);
    $namespace = empty($namespaceFrags) ? '' : 'namespace ' . implode('\\',
            $namespaceFrags) . ';';
    $function = reset($function);

    if (function_exists($functionFqn)) {
        return;
    }

    $code = <<< PHP
{$namespace}
function {$function}(){
	return \\tad\\FunctionMockerLe\\callback('{$functionFqn}', func_get_args());
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
    $functionFqn = '\\' . ltrim($function, '\\');
    Store::$defined[$functionFqn] = Store::undefined($functionFqn);
}

/**
 * Bulk undefines all functions defined by Function Mocker LE or a group of
 * functions.
 *
 * Undefined functions will throw an UndefinedFunctionException when called;
 * the method will tear down all the environments too.
 *
 * @param array|null $functions
 */
function undefineAll(array $functions = null) {
    foreach (Store::$environments as $name => $env) {
        $env->tearDown();
    }

    Store::$environments = [];

    if (null === $functions) {
        undefineAll(array_keys(Store::$defined));

        return;
    }

    foreach ($functions as $function) {
        undefine($function);
    }
}

/**
 * Sets up an environment calling its `setUp` method.
 *
 * @param \tad\FunctionMockerLe\Environment $environment
 * @param mixed|null                        $arg1       One or more additional
 *                                                 arguments that will be
 *                                                 passed to the environment.
 */
function setupEnvironment(Environment $environment, $arg1 = null) {
    $args                              = func_get_args();
    $env                               = array_shift($args);
    Store::$environments[$env->name()] = $env;


    if (count($args) === 0) {
        $env->setUp();
    } else {
        call_user_func_array([$env, 'setUp'], $args);
    }
}

/**
 * Calls a specific environment `tearDown` method.
 *
 * @param string $envName    The name of an environment set up using hte
 *                           `setupEnvironment` function.
 */
function tearDownEnvironment($envName) {
    if (!array_key_exists($envName, Store::$environments)) {
        throw new \InvalidArgumentException("No environment {$envName} was ever set up.");
    }

    Store::$environments[$envName]->tearDown();
}

/**
 * Calls the replacement function with the specified arguments.
 *
 * @param       string $function The function name
 * @param array        $args     An array of arguments to call the function with
 *
 * @return mixed|null
 */
function callback($function, array $args = []) {
    $functionFqn = '\\' . ltrim($function, '\\');

    if (!isset(Store::$defined[$functionFqn])) {
        return null;
    }

    return call_user_func_array(Store::$defined[$functionFqn], $args);
}
