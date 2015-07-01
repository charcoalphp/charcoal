Charcoal Core
=============

[![Build Status](https://travis-ci.org/locomotivemtl/charcoal-core.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-core)


The `charcoal-core` module contains abstract classes and interfaces as well as basic functionalities to create a Charcoal Project.
It is typically used with [`charcoal-base`](https://github.com/locomotivemtl/charcoal-base), which contains more concrete classes (Action, Asset, Email, Module Objet, Property<sup>\[1\]</sup>, Template and Widget)

1. Although the core Property concepts are defined in this module, most _useful_ property types can be found in `charcoal-base`.

## How to Install

Except for development purposes, this module should never be run by itself or as a standalone. Therefore, the preferred way to install this module is to require it as a `composer` dependency in a project.

```shell
$ composer require locomotivemtl/charcoal-core
```

## Dependencies and Requirements

Charcoal depends on:

- **PHP** 5.5+
  - with [_PHP Generators_](http://php.net/generators) and the [`password_hash`](http://php.net/password-hash) methods.
- **MySQL**
  - with [_PDO_](http://php.net/pdo)
  - Other databases are currently not supported
- **Apache**
  - with _mod_rewrite_

## Build System(s)

Charcoal uses:

- [**Composer**](http://getcomposer.org/) is the preferred way of installing Charcoal modules and projects.
- [**Grunt**](http://gruntjs.com/) is used to build the assets from source and also to run various scripts (linters, unit tests) automatically.
  - The CSS is generated with [(lib)Sass](http://sass-lang.com/libsass)
- [**Bower**](http://bower.io/) is used for managing external dependencies.
- [**NPM**](https://npmjs.com/) is needed for Bower and Grunt.


## Table of Contents

The core concepts (namespaces) defined in Charcoal Core are:

- `Cache`, for the cache interfaces as well as a few drivers.
  - Available cache types: `apc`, `memcache` and `noop`.
  - Extra interface: `CacheableInterface` / `CacheableTrait` for objects that can be stored in the cache.
  - Default cache should typically be _memcache_.
- `Config`, for the configuration objects.
  - Extra interface: `ConfigurableInterface` / `ConfigurableTrait` for objects that can be defined with a `*Config` object.
- `Core`, for core patterns, classes and traits.
  - `AbstractFactory` / `FactoryInterface`: a base class for all Factories in Charcoal.
  - `IndexableInterface` / `IndexableTrait`:
    -  Defines `set_id()`, `id()`, `set_key()` and `key()`.
  - `StringFormat`: a helper class to format strings, mostly for final output within templates & widgets:
    - `unicode()`, `strip_tags()`, `unaccents()` and `alphanumeric()`
- `Encoder`, for the encoder interfaces as well as a few drivers:
  - Available encoder types: `base64`.
  - Note that this is a simple encoding library,**not** a cryptographic lib.
- `Loader`, for everything that can be loaded.
  - Base loaders: `AbstractLoader`, `FileLoader`
  - Extra interface: `LoadableInterface` / `LoadableTrait`
- `Metadata`, for object definition through standardized metadata.
  - Extra interfaces: `DescribableInterface` / `DescribableTrait` for objects
- `Model`, for base domain model objects.
  - Extra interface: `CategorizableInterface` / `CategorizableTrait`
  - Extra interface: `CategoryInterface` / `CategoryTrait`
  - Extra interface: `IndexableInterface` / `IndexableTrait` for models that can be loaded with `id()` (and `key()`)
  - Extra interface: `RoutableInterface` / `RoutableTrait`
- `Property`, the building blocks of models (through metadata)
  - Only the core property concepts are defined in the `charcoal-core` module. Extra property types can be found in `charcoal-base`
- `Source`, for storage (typically accessed with a Loader)
  - Extra interface:
- `Validator`, to validate objects / models.
  - Extra interface: `ValidatableInterface` / `ValidatableTrait` for objects that can be validated with a validator.
- `View` for rendering objects with templates.
  - 4 core concepts: `View` and `ViewController`, `ViewEngine` and `ViewTemplate`
  - Available view engines: `php_mustache` and `mustache`
  - Extra interface: `ViewableInterface` / `ViewableTrait` for objects that can be rendered

## Development

### Coding Style

All Charcoal modules follow the same coding style and `charcoal-core` is no exception. For PHP:

- [_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md), except for
  - Method names MUST be declared in `snake_case`.
- [_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
- [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), autoloading is therefore provided by _Composer_
- [_phpDocumentor_](http://phpdoc.org/)
- Arrays should be written in short notation (`[]` instead of `array()`)

Coding styles are  enforced with `grunt phpcs` ([_PHP Code Sniffer_](https://github.com/squizlabs/PHP_CodeSniffer)). The actual ruleset can be found in `phpcs.xml`.

> 👉 To fix minor coding style problems, run `grunt phpcbf` ([_PHP Code Beautifier and Fixer_](https://github.com/squizlabs/PHP_CodeSniffer)). This tool uses the same ruleset as *phpcs* to automatically correct coding standard violations.

The main PHP structure follow the [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md) standard. Autoloading is therefore provided by _Composer_.

For JavaScript, the following coding style is enforced:

- **TBD**

### Automated Checks

Most quality checker tools can be run with _Grunt_. They are:

- `grunt phpunit`, to run the Test Suite.
- `grunt phpcs`, to ensure coding style compliance.
- `grunt jsonlint`, to validate JSON files.

All three tools can also be run from `grunt watch`.

To ensure a clean code base, pre-commit git hooks should be installed on all development environments.

### Continuous Integration

- [Travis](https://travis-ci.org/)
- [Scrutinizer](https://scrutinizer-ci.com/)
- [Code Climate](https://codeclimate.com/)

### Unit Tests

Every class, method, and function should be covered by unit tests. PHP code can be tested with [_PHPUnit_](https://phpunit.de/) and JavaScript code with [_QUnit_](https://qunitjs.com/).

## Authors

- Mathieu Ducharme <mat@locomotive.ca>

## Changelog

- Unreleased.

## TODOs

- Add `SingletonInterface` / `SingletonTrait`
- Translation (l10n) module
- The main `Charcoal\Charcoal` class should be moved to `charcoal-base` and not used anywhere directly, if possible
