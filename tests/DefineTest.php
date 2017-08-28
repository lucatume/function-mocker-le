<?php

use PHPUnit\Framework\TestCase;
use function tad\FunctionMockerLe\define;

class DefineTest extends TestCase {

	/**
	 * It should allow defining a non existing function
	 *
	 * @test
	 */
	public function should_allow_defining_a_non_existing_function() {
		define('foo', function () {
			return 'bar';
		});

		$this->assertEquals('bar', foo());
	}

	/**
	 * It should allow defining a non existing namespaced function
	 *
	 * @test
	 */
	public function should_allow_defining_a_non_existing_namespaced_function() {
		define('\some\bar\foo', function () {
			return 'bar';
		});

		$this->assertEquals('bar', \some\bar\foo());
	}

	/**
	 * It should allow redefining an already defined function more than once
	 *
	 * @test
	 */
	public function should_allow_redefining_an_already_defined_function_more_than_once() {
		define('f1', function () {
			return 'one';
		});

		$this->assertEquals('one', f1());

		define('f1', function () {
			return 'two';
		});

		$this->assertEquals('two', f1());

		define('f1', function () {
			return 'three';
		});

		$this->assertEquals('three', f1());
	}

	/**
	 * It should allow using a defined function to define a function
	 *
	 * @test
	 */
	public function should_allow_using_a_defined_function_to_define_a_function() {
		define('f234', function () {
			return 'foo';
		});
		define('f567', function () {
			return f234() . 'bar';
		});

		$this->assertEquals('foobar', f567());
	}

	/**
	 * It should pass the function call arguments to the callback
	 *
	 * @test
	 */
	public function should_pass_the_function_call_arguments_to_the_callback() {
		define('add', function ($p1, $p2) {
			return $p1 + $p2;
		});

		$this->assertEquals(2, add(1, 1));
		$this->assertEquals(5, add(3, 2));
		$this->assertEquals(5, add(10, -5));
		$this->assertEquals(7, add(10, -3));
	}

	/**
	 * It should throw if trying to define a function not defined by the library
	 *
	 * @test
	 */
	public function should_throw_if_trying_to_define_a_function_not_defined_by_the_library() {
		eval('function foobar(){};');

		$this->expectException(RuntimeException::class);

		define('foobar', function () {
			return 2389;
		});
	}
}
