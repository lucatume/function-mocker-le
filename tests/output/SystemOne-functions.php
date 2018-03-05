<?php

/**
 * Short description.
 *
 * Long description.
 *
 * @since some-version
 *
 * @param $arg1
 * @param $string2
 */
function aFunction(array $arg1, $string2)
{
    return \tad\FunctionMockerLe\callback(__FUNCTION__, func_get_args());
}