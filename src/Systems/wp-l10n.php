<?php
/**
 * Replacements for WordPress localization functions
 */

//translate
if (!function_exists('translate')) {
  function translate($string) {
    return $string;
  }
}

if (!function_exists('translate_with_gettext_context')) {
  function translate_with_gettext_context($text) {
    return $text;
  }
}

if (!function_exists('__')) {
  function __($string) {
    return $string;
  }
}

if (!function_exists('esc_attr__')) {
  function esc_attr__($string) {
    return htmlentities($string);
  }
}

if (!function_exists('esc_html__')) {
  function esc_html__($string) {
    return htmlentities($string);
  }
}

if (!function_exists('_e')) {
  function _e($string) {
    echo $string;
  }
}

if (!function_exists('esc_attr_e')) {
  function esc_attr_e($string) {
    echo $string;
  }
}

if (!function_exists('esc_html_e')) {
  function esc_html_e($string) {
    echo $string;
  }
}

if (!function_exists('_x')) {
  function _x($string) {
    return $string;
  }
}

if (!function_exists('_ex')) {
  function _ex($string) {
    echo $string;
  }
}

if (!function_exists('esc_attr_x')) {
  function esc_attr_x($string) {
    return $string;
  }
}

if (!function_exists('esc_html_x')) {
  function esc_html_x($string) {
    return $string;
  }
}

if (!function_exists('_n')) {
  function _n($single, $plural, $number) {
    return $number == 1 ? $single : $plural;
  }
}

if (!function_exists('_nx')) {
  function _nx($single, $plural, $number) {
    return $number == 1 ? $single : $plural;
  }
}

if (!function_exists('_n_noop')) {
  function _n_noop($singular, $plural, $domain = NULL) {
    return [0 => $singular, 1 => $plural, 'singular' => $singular, 'plural' => $plural, 'context' => NULL, 'domain' => $domain];
  }
}

if (!function_exists('_nx_noop')) {
  function _nx_noop($singular, $plural, $context, $domain = NULL) {
    return [0 => $singular, 1 => $plural, 2 => $context, 'singular' => $singular, 'plural' => $plural, 'context' => $context, 'domain' => $domain];
  }
}

if (!function_exists('translate_nooped_plural')) {
  function translate_nooped_plural($nooped_plural, $count, $domain = 'default') {
    return $count > 1 ? $nooped_plural['plural'] : $nooped_plural['singular'];
  }
}

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
];
