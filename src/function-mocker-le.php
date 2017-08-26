<?php

namespace tad\FunctionMockerLe;

global $__fmle_callbacks;

$__fmle_callbacks = [];

function define( $function, $callback ) {
	global $__fmle_callbacks;
	
	if(!isset($__fmle_callbacks[$function]) && function_exists($function)){
		$message = "Function {$function} has been defined before Function Mocker LE did its first redefinition attempt.";
		$message .= "\nIf you need to redefine an existing function use the Function Mocker library (lucatume/function-mocker)."
		throw new RunTimeException($message);
	}

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


