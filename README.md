Charcoal Core
=============

[![Build Status](https://api.travis-ci.com/locomotivemtl/charcoal-core.svg?token=pGHp1Fn8uKqLp5exqFVS)](https://magnum.travis-ci.com/locomotivemtl/charcoal-core)


The `charcoal-core` module contains abstract classes and interfaces as well as basic functionalities to create a Charcoal Project.
It is typically used with `charcoal-base`, which contains more concrete classes (Action, Asset, Email, Module Objet, Property*, Template and Widget)

* Although the core Property concepts are defined in this module, most useful property types can be found in `charcoal-base`.

# Table of Contents

The core concepts (namespaces) defined in Charcoal Core are:
- `Cache`, for the cache interfaces as well as a few drivers.
  - Available cache types: `apc`, `memcache` and `noop`
  - Extra interface: `CacheableInterface` / `CacheableTrait` for objects that can be stored in the cache.
- `Config`, for the configuration objects.
  - Extra interface: `ConfigurableInterface` / `ConfigurableTrait` for objects that can be defined with config.
- `Core`, for core patterns.
  - Currently, only `AbstractFactory`
  - @todo: `SingletonInterface` / `SingletonTrait`
- `Encoder`, for the encoder interfaces as well as a few drivers:
  - Available encoder types: `base64`
- `Loader`, for everything that can be loaded.
  - Base loaders: `AbstractLoader`, `FileLoader`
  - Extra interface: `LoadableInterface` / `LoadableTrait`
- `Metadata`, for object definition through standardized metadata.
  - Extra interfaces: `DescribableInterface` / `DescribableTrait`
- `Model`, for base domain model objects.
  - Extra interface: `CategorizableInterface` / `CategorizableTrait`
  - Extra interface: `CategoryInterface` / `CategoryTrait`
  - Extra interface: `IndexableInterface` / `IndexableTrait` for models that can be loaded with `id()` (and `key()`)
  - Extra interface: `RoutableInterface` / `RoutableTrait`
- `Property`, the building blocks of models (through metadata)
  - Only the core property concepts are defined in the `charcoal-core` module. Extra property types can be found in `charcoal-base`
- `Source`, for storage (typically accessed with a Loader)
  - Extra interface:
- `Validator`, to validate objects
  - Extra interface: `ValidatableInterface` / `ValidatableTrait` for objects that can be validated with a validator.
- `View` for rendering objects with templates.
  - 4 core concepts: `View` and `ViewController`, `ViewEngine` and `ViewTemplate`
  - Available view engines: `php_mustache` and `mustache`
  - Extra interface: `ViewableInterface` / `ViewableTrait` for objects that can be rendered

# Authors
- Mathieu Ducharme <mat@locomotive.ca>

# Changelog
- Unreleased.

# TODOs
- `IndexableInterface` (and trait) should probably be moved in `Charcoal\Core` namespace.
- `CategorizableInterface`, `CategoryInterface` and `RoutableInterface` (and traits) should probably be moved to `charcoal-base`.
- Mustache template loader should support `.mustache` file names.
- The main `Charcoal\Charcoal` class should be moved to `charcoal-base` and not used anywhere directly, if possible
