<?php

namespace tad\FunctionMockerLe\Systems;


use tad\FunctionMockerLe\System;

class WordPress implements System {

  /**
   * @var array
   */
  protected $defined = [];

  /**
   * Returns the system slug.
   *
   * @return string
   */
  public function name() {
    return 'WordPress';
  }

  /**
   * Defines, using function-mocker-le API, the functions to define.
   *
   * @return void
   */
  public function setUp(...$args) {
    include __DIR__ . '/wp-l10n.php';
    include __DIR__ . '/wp-utils.php';
    include __DIR__ . '/wp-filters.php';
  }

  /**
   * Tears down the definitions made by the system in the setup phase.
   *
   * @return void
   */
  public function tearDown() {
    \tad\FunctionMockerLe\undefineAll($this->defined);
  }

  /**
   * Returns a list of the functions defined by the System.
   *
   * @return array
   */
  public function defined() {
    return [
      'translate',
      'translate_with_gettext_context',
      '__',
      'esc_attr__',
      'esc_html__',
      '_e',
      'esc_attr_e',
      'esc_html_e',
      '_x',
      '_ex',
      'esc_attr_x',
      'esc_html_x',
      '_n',
      '_nx',
      '_n_noop',
      '_nx_noop',
      'translate_nooped_plural',
      'trailingslashit',
      'untrailingslashit',
      '__return_true',
      '__return_false',
      '__return_null',
      '__return_zero',
      '__return_empty_array',
      '__return_empty_string',
      'add_action',
      'add_filter',
      'do_action',
      'do_action_ref_array',
      'apply_filters',
      'apply_filters_ref_array',
      'has_action',
      'has_filter',
      'did_action',
      'remove_action',
      'remove_filter',
      'doing_action',
      'doing_filter',
      'current_filter',
    ];
  }
}
