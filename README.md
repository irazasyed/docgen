# Docgen for Laravel Facade

[![Latest Version on Packagist](https://img.shields.io/packagist/v/irazasyed/docgen.svg?style=flat-square)](https://packagist.org/packages/irazasyed/docgen)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/irazasyed/docgen/ci.yml?branch=main&label=tests&style=flat-square)](https://github.com/irazasyed/docgen/actions?query=workflow%3Aci+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/irazasyed/docgen/code-style.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/irazasyed/docgen/actions?query=workflow%3A"Code+Style"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/irazasyed/docgen.svg?style=flat-square)](https://packagist.org/packages/irazasyed/docgen)

> 📖 Streamline your Laravel package development with automatic facade documentation using Docgen for Laravel Facade.

![Docgen](https://user-images.githubusercontent.com/1915268/225490242-13903347-b8a7-40ea-897b-0a5429208cbb.jpg)

## Introduction

Introducing the Docgen for Laravel packages, the ultimate solution for Laravel package developers.
This heroic package automatically generates documentation for your Laravel package facade, eliminating the tedious task of maintaining it yourself.
With this package, IDEs will provide you and the developers using your package with method suggestions, making your development journey a breeze.

## Installation

You can install the package via composer:

```bash
composer require irazasyed/docgen
```

## Usage

```php
use Docgen\Docgen;

$facade = \Namespace\To\Facade::class;

Docgen::generate($facade)->apply();
```

## API

### `generate(string|array $classes, array $globalExcludedMethods = [])`

Generate the documentation for given classes.

#### Parameters

- `$classes` - The class name or an array of `[class => (optional) [excluded methods]]` to generate documentation using its methods.
- `$globalExcludedMethods` - (optional) An array of methods to be excluded when generating docs.

#### Returns

`Docgen` - The Docgen instance.

#### Example

```php
Docgen::generate([
    //  Class Name => [Excluded Methods]
    \App\Some\Class::class => ['method1', 'method2'],
    \App\Some\OtherClass::class,
])->apply(\Namespace\To\Facade::class); // Apply to Facade.
```

### `getDocBlock()`

Get the generated documentation.

#### Returns

`string` - The generated documentation.

### `apply(string $className = '')`

Apply the generated documentation to the given class name or defaults to the class used to generate docs.

#### Parameters

- `$className` - (optional) The class name to apply the generated documentation to.

#### Returns

`bool` - True if it was successful, false otherwise.

## TODO

- [ ] Add console command for easy generation.
- [ ] Add github action to automatically maintain docs.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Irfaq Syed](https://github.com/irazasyed)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
