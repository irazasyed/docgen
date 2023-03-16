<?php

namespace Docgen\Tests\Fixtures;

use Illuminate\Support\Facades\Facade;

class LaravelFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SomeClass::class;
    }
}
