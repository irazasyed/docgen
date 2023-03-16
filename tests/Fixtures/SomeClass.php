<?php

namespace Docgen\Tests\Fixtures;

class SomeClass
{
    public function __construct()
    {
    }

    public function test($param)
    {
        return $param;
    }

    public function anotherMethod()
    {
        return $this;
    }

    private function notVisible()
    {
        return $this;
    }
}
