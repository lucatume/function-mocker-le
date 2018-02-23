<?php
/**
 * Filters and actions replacements.
 */

use tad\FunctionMockerLe\Systems\Filters;

if (!function_exists('add_action')) {
  function add_action($action, $function_to_add, $priority = 10, $accepted_args = 1) {
    Filters::instance()->addAction($action, $function_to_add, $priority, $accepted_args);
    return true;
  }
}
if (!function_exists('add_filter')) {
  function add_filter($action, $function_to_add, $priority = 10, $accepted_args = 1) {
    Filters::instance()->addFilter($action, $function_to_add, $priority, $accepted_args);
    return true;
  }
}
if (!function_exists('do_action')) {
  function do_action($action, ...$args) {
    Filters::instance()->doAction($action, $args);
  }
}
if (!function_exists('do_action_ref_array')) {
  function do_action_ref_array($action, array $args) {
    Filters::instance()->doAction($action, $args);
  }
}
if (!function_exists('apply_filters')) {
  function apply_filters($filter, ...$args) {
    return Filters::instance()->applyFilters($filter, $args);
  }
}
if (!function_exists('apply_filters_ref_array')) {
  function apply_filters_ref_array($filter, array $args) {
    return Filters::instance()->applyFilters($filter, $args);
  }
}
if (!function_exists('has_action')) {
  function has_action($action, $callback = null) {
    return Filters::instance()->hasAction($action, $callback);
  }
}
if (!function_exists('has_filter')) {
  function has_filter($filter, $callback = null) {
    return Filters::instance()->hasFilter($filter, $callback);
  }
}
if (!function_exists('did_action')) {
  function did_action($action) {
    return Filters::instance()->didAction($action);
  }
}
if (!function_exists('remove_action')) {
  function remove_action($action, $function_to_remove, $priority = 10) {
    return Filters::instance()->removeAction($action, $function_to_remove, $priority);
  }
}
if (!function_exists('remove_filter')) {
  function remove_filter($filter, $function_to_remove, $priority = 10) {
    return Filters::instance()->removeFilter($filter, $function_to_remove, $priority);
  }
}
if (!function_exists('doing_action')) {
  function doing_action($action = null) {
    return Filters::instance()->doingAction($action);
  }
}
if (!function_exists('doing_filter')) {
  function doing_filter($filter = null) {
    return Filters::instance()->doingFilter($filter);
  }
}
if (!function_exists('current_filter')) {
  function current_filter() {
    return Filters::instance()->currentFilter();
  }
}

if (!function_exists('remove_all_actions')) {
  function remove_all_actions($tag, $priority = false) {
    Filters::instance()->removeAllActions($tag, $priority);
  }
}

if (!function_exists('remove_all_filters')) {
  function remove_all_filters($filter, $priority = false) {
    Filters::instance()->removeAllFilters($filter, $priority);
  }
}
