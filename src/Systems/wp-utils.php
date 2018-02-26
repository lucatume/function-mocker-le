<?php
/**
 * Replacements for WordPress utility functions
 */

if (!function_exists('trailingslashit')) {
    function trailingslashit($string) {
        return rtrim($string, '/\\') . '/';
    }
}

if (!function_exists('untrailingslashit')) {
    function untrailingslashit($string) {
        return rtrim($string, '/\\');
    }
}

if (!function_exists('__return_true')) {
    function __return_true() {
        return true;
    }
}
if (!function_exists('__return_false')) {
    function __return_false() {
        return false;
    }
}
if (!function_exists('__return_null')) {
    function __return_null() {
        return NULL;
    }
}
if (!function_exists('__return_zero')) {
    function __return_zero() {
        return 0;
    }
}
if (!function_exists('__return_empty_array')) {
    function __return_empty_array() {
        return [];
    }
}
if (!function_exists('__return_empty_string')) {
    function __return_empty_string() {
        return '';
    }
}
