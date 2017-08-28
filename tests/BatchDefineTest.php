<?php

use PHPUnit\Framework\TestCase;
use function tad\FunctionMockerLe\defineAll;

class BatchDefineTest extends TestCase {

	/**
	 * It should allow defining a batch of functions
	 *
	 * @test
	 */
	public function should_allow_defining_a_batch_of_functions() {
		$batch = ['functionOne', 'functionTwo', 'functionThree'];

		defineAll($batch, function () {
			return 2389;
		});

		foreach ($batch as $ƒ) {
			$this->assertTrue(function_exists($ƒ));
			$this->assertEquals(2389, $ƒ());
		}
	}

	/**
	 * It should allow batch redefine
	 *
	 * @test
	 */
	public function should_allow_batch_redefine() {
		$batch = ['functionFour', 'functionFive', 'functionSix'];

		defineAll($batch, function () {
			return 2389;
		});

		foreach ($batch as $ƒ) {
			$this->assertTrue(function_exists($ƒ));
			$this->assertEquals(2389, $ƒ());
		}

		defineAll($batch, function () {
			return 4567;
		});

		foreach ($batch as $ƒ) {
			$this->assertEquals(4567, $ƒ());
		}
	}
}
