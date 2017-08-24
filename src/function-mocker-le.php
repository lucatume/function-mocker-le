<?php

namespace tad\FunctionMockerLe;

global $__fmle_callbacks;

$__fmle_callbacks = [];

function define( $function, $callback ) {
	global $__fmle_callbacks;

	$__fmle_callbacks[ $function ] = $callback;

	$function       = array_filter( explode( '\\', $function ) );
	$namespaceFrags = array_splice( $function, 0, count( $function ) - 1 );
	$namespace      = empty( $namespaceFrags ) ? '' : 'namespace ' . implode( '\\', $namespaceFrags ) . ';';
	$function       = reset( $function );
	$functionFqn    = empty( $namespace ) ? $function : trim( $namespace, ';' ) . '\\' . $function;

	if ( function_exists( $functionFqn ) ) {
		return;
	}

	$code = <<< PHP
{$namespace}
function {$function}(){
	global \$__fmle_callbacks;
	\$ƒ = \$__fmle_callbacks['{$function}'];
	
	return call_user_func_array(\$ƒ, func_get_args());
}
PHP;

	eval( $code );
}


