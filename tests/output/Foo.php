<?php

/*
* Auto-generated by the function-mocker-le package with the `fmle generate:system command`
* on 2018-03-03 23:57:09
*/
class Foo implements \tad\FunctionMockerLe\System
{
    public function name()
    {
        return 'Foo';
    }
    public function setUp(...$args)
    {
        include_once __DIR__ . '/FooFunctions.php';
    }
    public function tearDown()
    {
        \tad\FunctionMockerLe\undefineAll($this->defined());
    }
    public function defined()
    {
        return array('aFunction');
    }
}