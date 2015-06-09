Charcoal Core
=============

[![Build Status](https://api.travis-ci.com/locomotivemtl/charcoal-core.svg?token=pGHp1Fn8uKqLp5exqFVS)](https://magnum.travis-ci.com/locomotivemtl/charcoal-core)


The `charcoal-core` module contains abstract classes and interfaces as well as basic functionalities to create a Charcoal Project.
It is typically used with `charcoal-base`, which contains more concrete classes (Action, Asset, Email, Module Objet, Property*, Template and Widget)

* Although the core Property concepts are defined in this module, most useful property types can be found in `charcoal-base`.

# How to install

Except for development purpose, this module should never be run by itself or as standalone. Therefore, the preferred way to install this module is to require it as a `composer` dependency in a project.

`composer require locomotivemtl/charcoal-core`

# Dependencies and requirements

Charcoal depends on:
- PHP 5.5+
- MySQL (with PDO)
  - Other databases are currently not supported
- Apache with *mod_rewrite*

## Build system(s)

`composer` is the preferred way of installing Charcoal modules and projects.

`grunt` is used to build the assets from source and also run the various scripts (linters, unit tests) automatically. The CSS is generated with `sass`

The external javascript dependencies are managed with `bower`.


# Table of Contents

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
  - @todo: `SingletonInterface` / `SingletonTrait`
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

# Development

## Coding style

All Charcoal modules follow the same coding style and `charcoal-core` is no exception. For PHP:
- _PSR-1_, except for the _CamelCaps_ method name requirement
- _PSR-2_
- array should be written in short notation (`[]` instead of `array()`)
- Docblocks for _phpdocumentor_

Coding styles are  enforced with `grunt phpcs` (_PHP Code Sniffer_). The actual ruleset can be found in [phpcs.xml][phpcs.xml].

> 👉 To fix minor coding style problems, run `grunt phpcbf` (_PHP Code Beautifier and Fixer_). This tool use the same ruleset as *phpcs* to try and fix what can be done automatically.

The main PHP structure follow the _PSR-4_ standard. Autoloading is therefore provided by _Composer_.

For Javascript, the following coding style is enforced:
- **todo**

## Automated checks

Most quality checker tools can be run with grunt. They are:
- `grunt phpunit`, to run the Test Suite.
- `grunt phpcs`, to ensure coding style compliance
- `grunt jsonlint`, to ensure JSON files

It is possible to run all those tool automatically with `grunt watch`.

To ensure a clean code base, pre-commit git hooks should be installed on all development

## Continuous Integration

- Travis
- Scrutinizer
- Code Climate

## Unit tests

Every classes, methods and functions should be covered by unit tests. PHP code can be tested with _PHPUnit_ and Javascript code with _QUnit_.

# Authors
- Mathieu Ducharme <mat@locomotive.ca>

# Changelog
- Unreleased. 

# TODOs
- Translation (l10n) module
- The main `Charcoal\Charcoal` class should be moved to `charcoal-base` and not used anywhere directly, if possible
