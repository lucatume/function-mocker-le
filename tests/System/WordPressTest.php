<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 22/02/2018
 * Time: 13:44
 */

namespace tad\FunctionMockerLe\Systems;


use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class WordPressTest extends TestCase {

  /**
   * @var \tad\FunctionMockerLe\Systems\WordPress
   */
  protected $sut;

  /**
   * It should define all the defined functions
   *
   * @test
   */
  public function should_define_all_the_defined_functions() {
    $wp = new WordPress();

    $wp->setUp();

    foreach ($wp->defined() as $f) {
      $this->assertTrue(function_exists($f));
    }
  }

  /**
   * Test add_action
   */
  public function test_add_action() {
    $this->sut->setUp();
    $total = 0;

    $callback = function (...$ns) use (&$total) {
      Assert::assertTrue(doing_action());
      Assert::assertTrue(doing_action('foo-action'));
      Assert::assertFalse(doing_action('bar-action'));
      Assert::assertTrue(doing_filter());
      Assert::assertTrue(doing_filter('foo-action'));
      Assert::assertFalse(doing_filter('bar-action'));
      $total = array_sum($ns);
    };

    add_action('foo-action', $callback, 10, 3);

    $this->assertFalse(did_action('foo-action'));

    do_action('foo-action', 1, 2, 3);

    $this->assertTrue(has_action('foo-action'));
    $this->assertFalse(has_action('foo-action', '__return_true'));
    $this->assertTrue(has_action('foo-action', $callback));
    $this->assertEquals(6, $total);

    $this->assertTrue(did_action('foo-action'));

    remove_action('foo-action', $callback);

    $this->assertFalse(has_action('foo-action'));
    $this->assertFalse(has_action('foo-action', $callback));
    $this->assertTrue(did_action('foo-action'));
  }

  /**
   * Test remove_all_actions
   */
  public function test_remove_all_actions() {
    remove_all_actions('foo-action');
    remove_all_actions('foo-action', 10);

    add_action('foo-action', '__return_true');
    add_action('foo-action', '__return_true', 20);

    remove_all_actions('foo-action', 20);

    $this->assertTrue(has_action('foo-action'));

    remove_all_actions('foo-action', 10);

    $this->assertFalse(has_action('foo-action'));
  }

  /**
   * Test add_filter
   */
  public function test_add_filter() {
    $this->sut->setUp();

    $callback = function (...$ns) {
      Assert::assertTrue(doing_filter());
      Assert::assertTrue(doing_filter('foo-filter'));
      Assert::assertFalse(doing_filter('bar-filter'));
      Assert::assertFalse(doing_action());

      return array_sum($ns);
    };

    add_filter('foo-filter', $callback, 10, 3);

    $this->assertFalse('foo-filter' === current_filter());

    $this->assertEquals(6, apply_filters('foo-filter', 1, 2, 3));

    $this->assertTrue(has_filter('foo-filter'));
    $this->assertFalse(has_filter('foo-filter', '__return_true'));
    $this->assertTrue(has_filter('foo-filter', $callback));

    remove_filter('foo-filter', $callback);

    $this->assertFalse(has_filter('foo-filter'));
    $this->assertFalse(has_filter('foo-filter', $callback));
  }

  /**
   * Test remove_all_filters
   */
  public function test_remove_all_filters() {
    remove_all_filters('foo-filter');
    remove_all_filters('foo-filter', 10);

    add_filter('foo-filter', '__return_true');
    add_filter('foo-filter', '__return_true', 20);

    remove_all_filters('foo-filter', 20);

    $this->assertTrue(has_filter('foo-filter'));

    remove_all_filters('foo-filter', 10);

    $this->assertFalse(has_filter('foo-filter'));
  }

  /**
   * Test do_action_ref_array
   */
  public function test_do_action_ref_array() {
    do_action_ref_array('ref-action', ['foo', 'bar']);

    $out = null;
    add_action('ref-action', function ($one, $two) use (&$out) {
      $out = $one . $two;
    }, 10, 2);
    do_action_ref_array('ref-action', ['foo', 'bar']);

    $this->assertEquals('foobar', $out);
  }

  /**
   * Test apply_filters_ref_array
   */
  public function test_apply_filters_ref_array() {
    $this->assertEquals('foo', apply_filters_ref_array('ref-filter', ['foo', 'bar', 'baz']));

    add_filter('ref-filter', function ($one, $two, $three) {
      return $one . $two . $three;
    }, 10, 3);

    $this->assertEquals('foobarbaz', apply_filters_ref_array('ref-filter', ['foo', 'bar', 'baz']));
  }

  protected function tearDown() {
    $this->sut->tearDown();
  }

  protected function setUp() {
    $this->sut = new WordPress();
  }
}
