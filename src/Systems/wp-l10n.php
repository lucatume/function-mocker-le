<?php
/**
 * Replacements for WordPress localization functions
 */

//translate
if (!function_exists('translate')) {
  \tad\FunctionMockerLe\define('translate', function ($string) {
    return $string;
  });
}

if (!function_exists('translate_with_gettext_context')) {
  \tad\FunctionMockerLe\define('translate_with_gettext_context', function ($text) {
    return $text;
  });
}

if (!function_exists('__')) {
  \tad\FunctionMockerLe\define('__', function ($string) {
    return $string;
  });

}

if (!function_exists('esc_attr__')) {
  \tad\FunctionMockerLe\define('esc_attr__', function ($string) {
    return htmlentities($string);
  });
}

if (!function_exists('esc_html__')) {
  \tad\FunctionMockerLe\define('esc_html__', function ($string) {
    return htmlentities($string);
  });
}

if (!function_exists('_e')) {
  \tad\FunctionMockerLe\define('_e', function ($string) {
    echo $string;
  });
}

if (!function_exists('esc_attr_e')) {
  \tad\FunctionMockerLe\define('esc_attr_e', function ($string) {
    echo $string;
  });
}

if (!function_exists('esc_html_e')) {
  \tad\FunctionMockerLe\define('esc_html_e', function ($string) {
    echo $string;
  });
}

if (!function_exists('_x')) {
  \tad\FunctionMockerLe\define('_x', function ($string) {
    return $string;
  });
}

if (!function_exists('_ex')) {
  \tad\FunctionMockerLe\define('_ex', function ($string) {
    echo $string;
  });
}

if (!function_exists('esc_attr_x')) {
  \tad\FunctionMockerLe\define('esc_attr_x', function ($string) {
    return $string;
  });
}

if (!function_exists('esc_html_x')) {
  \tad\FunctionMockerLe\define('esc_html_x', function ($string) {
    return $string;
  });
}

if (!function_exists('_n')) {
  \tad\FunctionMockerLe\define('_n', function ($single, $plural, $number) {
    return $number == 1 ? $single : $plural;
  });
}

if (!function_exists('_nx')) {
  \tad\FunctionMockerLe\define('_nx', function ($single, $plural, $number) {
    return $number == 1 ? $single : $plural;
  });
}

if (!function_exists('_n_noop')) {
  \tad\FunctionMockerLe\define('_n_noop', function ($singular, $plural, $domain = null) {
    return [0 => $singular, 1 => $plural, 'singular' => $singular, 'plural' => $plural, 'context' => null, 'domain' => $domain];
  });
}

if (!function_exists('_nx_noop')) {
  \tad\FunctionMockerLe\define('_nx_noop', function ($singular, $plural, $context, $domain = null) {
    return [0 => $singular, 1 => $plural, 2 => $context, 'singular' => $singular, 'plural' => $plural, 'context' => $context, 'domain' => $domain];
  });
}

if (!function_exists('translate_nooped_plural')) {
  \tad\FunctionMockerLe\define('translate_nooped_plural', function ($nooped_plural, $count, $domain = 'default') {
    return $count > 1 ? $nooped_plural['plural'] : $nooped_plural['singular'];
  });
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
