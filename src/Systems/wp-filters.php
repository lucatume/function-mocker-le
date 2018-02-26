<?php
/**
 * Filters and actions replacements.
 */

use tad\FunctionMockerLe\Systems\Filters;

if ($unsafe || !function_exists('add_action')) {
    \tad\FunctionMockerLe\define('add_action', function ($action, $function_to_add, $priority = 10, $accepted_args = 1) {
        Filters::instance()->addAction($action, $function_to_add, $priority, $accepted_args);
        return true;
    });
}

if ($unsafe || !function_exists('add_filter')) {
    \tad\FunctionMockerLe\define('add_filter', function ($action, $function_to_add, $priority = 10, $accepted_args = 1) {
        Filters::instance()->addFilter($action, $function_to_add, $priority, $accepted_args);
        return true;
    });
}

if ($unsafe || !function_exists('do_action')) {
    \tad\FunctionMockerLe\define('do_action', function ($action, ...$args) {
        Filters::instance()->doAction($action, $args);
    });
}

if ($unsafe || !function_exists('do_action_ref_array')) {
    \tad\FunctionMockerLe\define('do_action_ref_array', function ($action, array $args) {
        Filters::instance()->doAction($action, $args);
    });
}

if ($unsafe || !function_exists('apply_filters')) {
    \tad\FunctionMockerLe\define('apply_filters', function ($filter, ...$args) {
        return Filters::instance()->applyFilters($filter, $args);
    });
}

if ($unsafe || !function_exists('apply_filters_ref_array')) {
    \tad\FunctionMockerLe\define('apply_filters_ref_array', function ($filter, array $args) {
        return Filters::instance()->applyFilters($filter, $args);
    });
}

if ($unsafe || !function_exists('has_action')) {
    \tad\FunctionMockerLe\define('has_action', function ($action, $callback = null) {
        return Filters::instance()->hasAction($action, $callback);
    });
}

if ($unsafe || !function_exists('has_filter')) {
    \tad\FunctionMockerLe\define('has_filter', function ($filter, $callback = null) {
        return Filters::instance()->hasFilter($filter, $callback);
    });
}

if ($unsafe || !function_exists('did_action')) {
    \tad\FunctionMockerLe\define('did_action', function ($action) {
        return Filters::instance()->didAction($action);
    });
}

if ($unsafe || !function_exists('remove_action')) {
    \tad\FunctionMockerLe\define('remove_action', function ($action, $function_to_remove, $priority = 10) {
        return Filters::instance()->removeAction($action, $function_to_remove, $priority);
    });
}

if ($unsafe || !function_exists('remove_filter')) {
    \tad\FunctionMockerLe\define('remove_filter', function ($filter, $function_to_remove, $priority = 10) {
        return Filters::instance()->removeFilter($filter, $function_to_remove, $priority);
    });
}

if ($unsafe || !function_exists('doing_action')) {
    \tad\FunctionMockerLe\define('doing_action', function ($action = null) {
        return Filters::instance()->doingAction($action);
    });
}

if ($unsafe || !function_exists('doing_filter')) {
    \tad\FunctionMockerLe\define('doing_filter', function ($filter = null) {
        return Filters::instance()->doingFilter($filter);
    });
}

if ($unsafe || !function_exists('current_filter')) {
    \tad\FunctionMockerLe\define('current_filter', function () {
        return Filters::instance()->currentFilter();
    });
}

if ($unsafe || !function_exists('remove_all_actions')) {
    \tad\FunctionMockerLe\define('remove_all_actions', function ($tag, $priority = false) {
        Filters::instance()->removeAllActions($tag, $priority);
    });
}

if ($unsafe || !function_exists('remove_all_filters')) {
    \tad\FunctionMockerLe\define('remove_all_filters', function ($filter, $priority = false) {
        Filters::instance()->removeAllFilters($filter, $priority);
    });
}
