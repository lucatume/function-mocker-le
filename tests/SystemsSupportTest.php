<?php

use PHPUnit\Framework\TestCase;
use tad\FunctionMockerLe\System;
use function tad\FunctionMockerLe\defineAll;
use function tad\FunctionMockerLe\setupSystem;
use function tad\FunctionMockerLe\tearDownSystem;
use function tad\FunctionMockerLe\undefineAll;

class SystemsSupportTest extends TestCase {

	/**
	 * @var System
	 */
	protected $system;

	/**
	 * It should define any function provided by the system
	 *
	 * @test
	 */
	public function should_define_any_function_provided_by_the_system() {
		$system = $this->prophesize(System::class);
		$system->name()->willReturn('fooSystem');
		$system->setUp()->will(function () {
			\tad\FunctionMockerLe\define('foo', function () {
				return 'foo';
			});
			defineAll(['bar', 'baz'], function () {
				return 23;
			});
		});

		setupSystem($system->reveal());

		$this->assertEquals('foo', foo());
		$this->assertEquals(23, bar());
		$this->assertEquals(23, baz());
	}

	/**
	 * It should throw if trying to tearDown a non set up system
	 *
	 * @test
	 */
	public function should_throw_if_trying_to_tear_down_a_non_set_up_system() {
		$this->expectException(InvalidArgumentException::class);

		tearDownSystem('someSystem');
	}

	/**
	 * It should call tearDown on the system
	 *
	 * @test
	 */
	public function should_call_tear_down_on_the_system() {
		$system = $this->prophesize(System::class);
		$system->name()->willReturn('fooSystem');
		$system->setUp()->willReturn(null);
		$system->tearDown()->shouldBeCalled();

		setupSystem($system->reveal());

		tearDownSystem('fooSystem');
	}

	/**
	 * Test undefine_all will call tearDown on all systems
	 */
	public function test_undefine_all_will_call_tear_down_on_all_systems() {
		$system1 = $this->prophesize(System::class);
		$system1->name()->willReturn('one');
		$system1->setUp()->willReturn(null);
		$system1->tearDown()->shouldBeCalled();
		$system2 = $this->prophesize(System::class);
		$system2->name()->willReturn('two');
		$system2->setUp()->willReturn(null);
		$system2->tearDown()->shouldBeCalled();

		setupSystem($system1->reveal());
		setupSystem($system2->reveal());

		undefineAll();
	}

	/**
	 * It should call the setUp method on the system with additional parameters
	 *
	 * @test
	 */
	public function should_call_the_set_up_method_on_the_system_with_additional_parameters() {
		$system = $this->prophesize(System::class);
		$system->name()->willReturn('systemWithArgs');
		$system->setUp('one', 'bar', 'baz')->shouldBeCalled();

		setupSystem($system->reveal(), 'one', 'bar', 'baz');
	}
}
