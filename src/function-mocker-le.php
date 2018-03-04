<?php

namespace tad\FunctionMockerLe;

use Prophecy\Prophet;

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
 * the method will tear down all the systems too.
 *
 * @param array|null $functions
 */
function undefineAll(array $functions = null) {
    foreach (Store::$systems as $name => $system) {
        $system->tearDown();
    }

    Store::$systems = [];

    if (null === $functions) {
        undefineAll(array_keys(Store::$defined));

        return;
    }

    foreach ($functions as $function) {
        undefine($function);
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
function setupSystem(System $system, $arg1 = null) {
    $args = func_get_args();
    $sys = array_shift($args);
    Store::$systems[$sys->name()] = $sys;


    if (count($args) === 0) {
        $sys->setUp();
    } else {
        call_user_func_array([$sys, 'setUp'], $args);
    }
}

/**
 * Calls a specific system `tearDown` method.
 *
 * @param string $systemName The name of a system set up using hte
 *                           `setupSystem` function.
 */
function tearDownSystem($systemName) {
    if (!array_key_exists($systemName, Store::$systems)) {
        throw new \InvalidArgumentException("No system {$systemName} was ever set up.");
    }

    Store::$systems[$systemName]->tearDown();
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

/**
 * Returns a method prophecy generated and handled by phpspec/prophecy
 *
 * Use this method to stub, mock and spy functions defined, via `define` or `callback`, by
 * Function Mocker LE.
 *
 * @param string $function
 *
 * @return \Prophecy\Prophecy\MethodProphecy
 *
 * @see \tad\FunctionMockerLe\define()
 * @see \tad\FunctionMockerLe\callback()
 */
function prophesize($function) {
    static $prophet;
    $functionArguments = func_get_args();
    array_shift($functionArguments);

    if (null === $prophet) {
        $prophet = new Prophet();
    }

    $frags = explode('\\', $function);
    $functionName = array_pop($frags);
    $namespace = count($frags) ? implode('\\', $frags) : false;
    $class = '_FMLEClass_' . str_replace('\\', '_', $function);
    $fullClassName = $namespace ? $namespace . '\\' . $class : $class;

    if (!class_exists($fullClassName)) {
        $classCode = sprintf('class %s{ public function %s(){} }', $class, $functionName);
        eval($classCode);
    }

    $prophecy = $prophet->prophesize($class);
    $methodProphecy = $prophecy->{$functionName}(...$functionArguments);

    define($function, function () use ($prophecy, $functionName) {
        $revealed = $prophecy->reveal();
        return call_user_func_array([$revealed, $functionName], func_get_args());
    });

    return $methodProphecy;
}
