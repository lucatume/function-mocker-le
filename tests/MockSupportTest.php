<?php

use PHPUnit\Framework\TestCase;
use function tad\FunctionMockerLe\define;

interface User {

	public function can();
}

class MockSupportTest extends TestCase {

	/**
	 * It should allow using a prophecy as callback
	 *
	 * @test
	 */
	public function should_allow_using_a_prophecy_as_callback() {
		$user = $this->prophesize(User::class);
		$user->can('edit_posts')->shouldBeCalledTimes(2);
		$user->can('read_postst')->shouldNotBeCalled();

		define('current_user_can', [$user->reveal(), 'can']);

		current_user_can('edit_posts');
		current_user_can('edit_posts');
	}

	/**
	 * It should allow using a mock and methods as callback
	 *
	 * @test
	 */
	public function should_allow_using_a_mock_and_methods_as_callback() {
		$user = $this->getMockBuilder(User::class)->getMock();
		$user->expects($this->at(0))->method('can')->with('edit_posts');
		$user->expects($this->at(1))->method('can')->with('edit_posts');

		define('current_user_can', [$user, 'can']);

		current_user_can('edit_posts');
		current_user_can('edit_posts');
	}
}
