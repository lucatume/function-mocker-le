<?php

use PHPUnit\Framework\TestCase;
use tad\FunctionMockerLe\UndefinedFunctionException;
use function tad\FunctionMockerLe\defineWithMap;
use function tad\FunctionMockerLe\randomName;
use function tad\FunctionMockerLe\undefine;
use function tad\FunctionMockerLe\undefineAll;

class UndefineTest extends TestCase {

	/**
	 * It should allow undefining a FMLE defined function
	 *
	 * @test
	 */
	public function should_allow_undefining_a_fmle_defined_function() {
		$f = function () {
			return 'foo';
		};

		$functions = [randomName(), randomName(), randomName()];

		defineWithMap(array_combine($functions, array_fill(0, 3, $f)));

		undefine(reset($functions));

		$functions[1]();
		$functions[2]();

		$this->expectException(UndefinedFunctionException::class);

		$functions[0]();
	}

	/**
	 * It should allow undefining all FMLE defined functions
	 *
	 * @test
	 */
	public function should_allow_undefining_all_fmle_defined_functions() {
		$f = function () {
			return 'foo';
		};

		$functions = [
			randomName() => $f,
			randomName() => $f,
			randomName() => $f,
		];

		defineWithMap($functions);

		undefineAll();

		foreach (array_keys($functions) as $function) {
			$this->expectException(UndefinedFunctionException::class);

			$function();
		}
	}

	/**
	 * It should allow undefining a group of FMLE defined functions
	 *
	 * @test
	 */
	public function should_allow_undefining_a_group_of_fmle_defined_functions() {
		$f = function () {
			return 'foo';
		};

		$functions = $functionsBuffer = [randomName(), randomName(), randomName()];

		defineWithMap(array_combine($functions, array_fill(0, 3, $f)));

		array_shift($functionsBuffer);

		undefineAll($functionsBuffer);

		$functions[0]();

		$this->expectException(UndefinedFunctionException::class);

		$functions[1]();

		$this->expectException(UndefinedFunctionException::class);

		$functions[2]();
	}
}
