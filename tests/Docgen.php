<?php

use Docgen\Docgen;
use Docgen\Tests\Fixtures\LaravelFacade;
use Docgen\Tests\Fixtures\SomeClass;

it('returns instance of docgen')
    ->expect(Docgen::generate(SomeClass::class))
    ->toBeInstanceOf(Docgen::class);

it('generates docblock for class')
    ->expect(Docgen::generate(SomeClass::class)->getDocBlock())
    ->toBeString()
    ->toBe(<<<'PHP'
/**
 * @see \Docgen\Tests\Fixtures\SomeClass
 *
 * @method static void test($param)
 * @method static void anotherMethod()
 *
 */
PHP
    );

it('generates docblock while excluding given methods')
    ->expect(Docgen::generate(SomeClass::class, ['anotherMethod'])->getDocBlock())
    ->toBeString()
    ->toBe(<<<'PHP'
/**
 * @see \Docgen\Tests\Fixtures\SomeClass
 *
 * @method static void test($param)
 *
 */
PHP
    );

it('throws a runtime exception when trying to apply docblock to non laravel facade class')
    ->defer(fn () => Docgen::generate(SomeClass::class)->apply())
    ->throws(\RuntimeException::class, 'Class is not a Laravel Facade.');

it('throws a runtime exception for non existent class')
    ->defer(fn () => Docgen::generate('SomeNonExistentClass')->apply())
    ->throws(\RuntimeException::class, 'Class SomeNonExistentClass does not exist.');

it('generates docblock and applies it to the facade class', function () {
    LaravelFacade::spy();

    $docgen = Docgen::generate(LaravelFacade::class);

    $mock = mock($docgen)
        ->shouldReceive('apply')
        ->once()
        ->andReturnTrue()
        ->getMock();

    expect($mock->apply())->toBeTrue();
});
