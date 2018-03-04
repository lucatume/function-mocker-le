<?php

function testFunctionThree($arg1, array $arg2) {
    return \tad\FunctionMockerLe\callback(__NAMESPACE__ . '\\' . __FUNCTION__, func_get_args());
}

//function __testFunctionFour( $arg1, array $arg2 ) {
//	return \tad\FunctionMockerLe\callback( __NAMESPACE__ . '\\' . __FUNCTION__, func_get_args() );
//}

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class ProphecyTest extends TestCase {

    /**
     * It should allow setting prophecies on a non existing functions
     *
     * @test
     */
    public function should_allow_setting_prophecies_on_a_non_existing_functions() {
        tad\FunctionMockerLe\prophesize('testFunction232323', 'foo', 'bar')
            ->willReturn('foo-bar');

        $this->assertEquals('foo-bar', testFunction232323('foo', 'bar'));
    }

    /**
     * It should allow setting prophecies on existing functions that use callback
     *
     * @test
     */
    public function should_allow_setting_prophecies_on_existing_functions_that_use_callback() {
        tad\FunctionMockerLe\prophesize('testFunctionThree', 'foo', Argument::type('array'))
            ->will(function ($args) {
                return $args[0] . array_sum($args[1]);
            });

        $this->assertEquals('foo1', testFunctionThree('foo', [1]));
        $this->assertEquals('foo3', testFunctionThree('foo', [1, 2]));
        $this->assertEquals('foo6', testFunctionThree('foo', [1, 2, 3]));
    }

    /**
     * It should allow setting prophecies on namespaced non-existing functions
     *
     * @test
     */
    public function should_allow_setting_prophecies_on_namespaced_non_existing_functions() {
        tad\FunctionMockerLe\prophesize('\foo\bar\testFunction232334', 'foo', 'bar')
            ->shouldBeCalled();

        \foo\bar\testFunction232334('foo', 'bar');
    }
}
