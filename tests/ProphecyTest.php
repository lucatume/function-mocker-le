<?php

namespace foo\bar {

    function testFunctionThree($arg1, array $arg2) {
        return \tad\FunctionMockerLe\callback(__FUNCTION__, func_get_args());
    }
}

namespace {

    function testFunctionThree($arg1, array $arg2) {
        return \tad\FunctionMockerLe\callback(__FUNCTION__, func_get_args());
    }


    use PHPUnit\Framework\TestCase;
    use Prophecy\Argument;

    class ProphecyTest extends TestCase {

        use tad\FunctionMockerLe\FunctionProphet;

        /**
         * It should allow setting prophecies on a non existing functions
         *
         * @test
         */
        public function should_allow_setting_prophecies_on_a_non_existing_functions() {
            $this->prophesizeFunction('testFunction232323', 'foo', 'bar')
                 ->willReturn('foo-bar');

            $this->assertEquals('foo-bar', testFunction232323('foo', 'bar'));
        }

        /**
         * It should allow setting prophecies on existing functions that use callback
         *
         * @test
         */
        public function should_allow_setting_prophecies_on_existing_functions_that_use_callback() {
            $this->prophesizeFunction('testFunctionThree', 'foo', Argument::type('array'))
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
            $this->prophesizeFunction('\foo\bar\testFunction232334', 'foo', 'bar')
                 ->shouldBeCalled();

            \foo\bar\testFunction232334('foo', 'bar');
        }

        /**
         * It should allow setting prophecies on namespaced functions using callback
         *
         * @test
         */
        public function should_allow_setting_prophecies_on_namespaced_functions_using_callback() {
            $this->prophesizeFunction('foo\\bar\\testFunctionThree', 'foo', Argument::type('array'))
                 ->will(function ($args) {
                     return $args[0] . array_sum($args[1]);
                 });

            $this->assertEquals('foo1', foo\bar\testFunctionThree('foo', [1]));
            $this->assertEquals('foo3', foo\bar\testFunctionThree('foo', [1, 2]));
            $this->assertEquals('foo6', foo\bar\testFunctionThree('foo', [1, 2, 3]));
        }
    }
}
