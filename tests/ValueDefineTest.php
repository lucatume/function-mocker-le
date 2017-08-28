<?php

use PHPUnit\Framework\TestCase;
use function tad\FunctionMockerLe\defineWithValueMap;
use function tad\FunctionMockerLe\randomName;

class ValueDefineTest extends TestCase {

	/**
	 * It should allow defining functions to return values with a map
	 *
	 * @test
	 */
	public function should_allow_defining_functions_to_return_values_with_a_map() {
		$map = [
			randomName() => 'one',
			randomName() => 'two',
			randomName() => 'three',
		];

		defineWithValueMap($map);

		foreach ($map as $function => $value) {
			$this->assertTrue(function_exists($function));
			$this->assertEquals($value, $function());
		}
	}
}
