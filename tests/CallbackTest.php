<?php

function __testFunctionOne( $arg1, array $arg2 ) {
	return \tad\FunctionMockerLe\callback( __NAMESPACE__ . '\\' . __FUNCTION__, func_get_args() );
}

function __testFunctionTwo( $arg1, array $arg2 ) {
	return \tad\FunctionMockerLe\callback( __NAMESPACE__ . '\\' . __FUNCTION__, func_get_args() );
}

use PHPUnit\Framework\TestCase;

class CallbackTest extends TestCase {

	/**
	 * It should allow calling a callback from a function
	 *
	 * @test
	 */
	public function should_allow_calling_a_callback_from_a_function() {
		$this->assertNull( __testFunctionOne( 'foo', [] ) );
	}

	/**
	 * It should allow later defining the return value of a callback function
	 *
	 * @test
	 */
	public function should_allow_later_defining_the_return_value_of_a_callback_function() {
		\tad\FunctionMockerLe\define( '__testFunctionTwo', function () {
			return 'foo';
		} );

		$this->assertEquals( 'foo', __testFunctionTwo( 'bar', [1,2,3] ) );

		\tad\FunctionMockerLe\define( '__testFunctionTwo', function ($arg1,array $arg2 = []) {
			return $arg1 . array_sum($arg2);
		} );

		$this->assertEquals( 'bar6', __testFunctionTwo( 'bar', [1,2,3] ) );
	}
}
