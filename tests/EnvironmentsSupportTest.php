<?php

use PHPUnit\Framework\TestCase;
use tad\FunctionMockerLe\Environment;
use function tad\FunctionMockerLe\defineAll;
use function tad\FunctionMockerLe\setupEnvironment;
use function tad\FunctionMockerLe\tearDownEnvironment;
use function tad\FunctionMockerLe\undefineAll;

class EnvironmentsSupportTest extends TestCase {

	/**
	 * @var Environment
	 */
	protected $env;

	/**
	 * It should define any function provided by the environment
	 *
	 * @test
	 */
	public function should_define_any_function_provided_by_the_environment() {
		$environment = $this->prophesize(Environment::class);
		$environment->name()->willReturn('fooEnvironment');
		$environment->setUp()->will(function () {
			\tad\FunctionMockerLe\define('foo', function () {
				return 'foo';
			});
			defineAll(['bar', 'baz'], function () {
				return 23;
			});
		});

		setupEnvironment($environment->reveal());

		$this->assertEquals('foo', foo());
		$this->assertEquals(23, bar());
		$this->assertEquals(23, baz());
	}

	/**
	 * It should throw if trying to tearDown a non set up environment
	 *
	 * @test
	 */
	public function should_throw_if_trying_to_tear_down_a_non_set_up_environment() {
		$this->expectException(InvalidArgumentException::class);

		tearDownEnvironment('someEnvironment');
	}

	/**
	 * It should call tearDown on the environment
	 *
	 * @test
	 */
	public function should_call_tear_down_on_the_environment() {
		$environment = $this->prophesize(Environment::class);
		$environment->name()->willReturn('fooEnvironment');
		$environment->setUp()->willReturn(null);
		$environment->tearDown()->shouldBeCalled();

		setupEnvironment($environment->reveal());

		tearDownEnvironment('fooEnvironment');
	}

	/**
	 * Test undefine_all will call tearDown on all environments
	 */
	public function test_undefine_all_will_call_tear_down_on_all_environments() {
		$environment1 = $this->prophesize(Environment::class);
		$environment1->name()->willReturn('one');
		$environment1->setUp()->willReturn(null);
		$environment1->tearDown()->shouldBeCalled();
		$environment2 = $this->prophesize(Environment::class);
		$environment2->name()->willReturn('two');
		$environment2->setUp()->willReturn(null);
		$environment2->tearDown()->shouldBeCalled();

		setupEnvironment($environment1->reveal());
		setupEnvironment($environment2->reveal());

		undefineAll();
	}

	/**
	 * It should call the setUp method on the environment with additional parameters
	 *
	 * @test
	 */
	public function should_call_the_set_up_method_on_the_environment_with_additional_parameters() {
		$environment = $this->prophesize(Environment::class);
		$environment->name()->willReturn('environmentWithArgs');
		$environment->setUp('one', 'bar', 'baz')->shouldBeCalled();

		setupEnvironment($environment->reveal(), 'one', 'bar', 'baz');
	}
}
