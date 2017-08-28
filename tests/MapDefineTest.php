<?php

use PHPUnit\Framework\TestCase;
use function tad\FunctionMockerLe\defineWithMap;
use function tad\FunctionMockerLe\randomName;

class MapDefineTest extends TestCase {

	/**
	 * It should allow defining a map of functions
	 *
	 * @test
	 */
	public function should_allow_defining_a_map_of_functions() {
		$f1 = function () {
			return 'foo';
		};
		$f2 = function () {
			return 'bar';
		};

		$map = [
			randomName() => $f1,
			randomName() => $f2,
			randomName() => $f2,
		];

		defineWithMap($map);

		foreach ($map as $function => $callback) {
			$this->assertTrue(function_exists($function));
			$this->assertEquals($callback(), $function());
		}
	}

	/**
	 * It should allow map redefine
	 *
	 * @test
	 */
	public function should_allow_map_redefine() {
		$f1 = function () {
			return 'foo';
		};
		$f2 = function () {
			return 'bar';
		};
		$f3 = function () {
			return 'zam';
		};

		$mapOne = [
			randomName() => $f1,
			randomName() => $f2,
			randomName() => $f2,
		];

		defineWithMap($mapOne);

		foreach ($mapOne as $function => $callback) {
			$this->assertTrue(function_exists($function));
			$this->assertEquals($callback(), $function());
		}

		$mapTwo = array_combine(array_keys($mapOne), [$f3, $f1, $f2,]);

		defineWithMap($mapTwo);

		foreach ($mapTwo as $function => $callback) {
			$this->assertTrue(function_exists($function));
			$this->assertEquals($callback(), $function());
		}
	}
}
