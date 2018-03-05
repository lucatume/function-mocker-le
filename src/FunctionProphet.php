<?php

namespace tad\FunctionMockerLe;

trait FunctionProphet {

    /**
     * Returns a method prophecy generated and handled by phpspec/prophecy
     *
     * Use this method to stub, mock and spy functions defined, via `define` or `callback`, by
     * Function Mocker LE.
     *
     * @param string $function
     *
     * @return \Prophecy\Prophecy\MethodProphecy
     *
     * @see \tad\FunctionMockerLe\define()
     * @see \tad\FunctionMockerLe\callback()
     */
    protected function prophesizeFunction($function) {
        if (!($this instanceof \PHPUnit_Framework_TestCase || $this instanceof \PHPUnit\Framework\TestCase)) {
            throw new \RuntimeException('The \\tad\\FunctionMockerLe\\FunctionProphet trait should only be used in PHPUnit testcases');
        }

        $functionArguments = func_get_args();
        array_shift($functionArguments);

        $frags = explode('\\', $function);
        $functionName = array_pop($frags);
        $namespace = count($frags) ? implode('\\', $frags) : false;
        $class = '_FMLEClass_' . str_replace('\\', '_', $function);
        $fullClassName = $namespace ? $namespace . '\\' . $class : $class;

        if (!class_exists($fullClassName)) {
            $classCode = sprintf('class %s{ public function %s(){} }', $class, $functionName);
            eval($classCode);
        }

        /** @var \Prophecy\Prophecy\ObjectProphecy $prophecy */
        $prophecy = $this->prophesize($class);
        $methodProphecy = $prophecy->{$functionName}(...$functionArguments);

        define($function, function () use ($prophecy, $functionName) {
            $revealed = $prophecy->reveal();
            return call_user_func_array([$revealed, $functionName], func_get_args());
        });

        return $methodProphecy;
    }
}