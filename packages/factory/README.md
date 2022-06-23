Charcoal Factory
================

Factories **create** (or build) dynamic PHP objects. Factories can resolve a _type_ to a FQN and create instance of this class with an optional given set of arguments, while ensuring a default base class.

[![Build Status](https://travis-ci.org/locomotivemtl/charcoal-factory.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-factory)

# Table of contents

-   How to install
    -   Dependencies
-   Factories
    -   Usage
    -   The resolver
    -   Class map / aliases
    -   Ensuring a type of object
    -   Setting a default type of object
    -   Constructor arguments
    -   Executing an object callback
-   Development
    -   Development dependencies
    -   Continus integration
    -   Coding style
    -   Authors
    -   Changelog
-   License

# How to install

The preferred (and only supported) way of installing _charcoal-factory_ is with **composer**:

```shell
★ composer require locomotivemtl/charcoal-factory
```

## Dependencies

-   [`PHP 5.5+`](http://php.net)
    -   Older versions of PHP are deprecated, therefore not supported for charcoal-factory.

> 👉 Development dependencies, which are optional when using charcoal-factory, are described in the [Development](#development) section of this README file.


# Factories

## Usage

Factories have only one purpose: to **create** / instanciate new PHP objects. Factory options should be set directly from the constructor:

```php
$factory = new \Charcoal\Factory\GenericFactory([
    // Ensure the created object is a Charcoal Model
    'base_class' => '\Charcoal\Model\ModelInterface',

    // An associative array of class map (aliases)
    'map' => [
        'foo' => '\My\Foo',
        'bar' => '\My\Bar'
    ],

    // Constructor arguments
    'arguments' => [
        $dep1,
        $dep2
    ],

    // Object callback
    'callback' => function (&obj) {
        $obj->do('foo');
    }
]);

// Create a "\My\Custom\Baz" object with the given arguments + callbck
$factory->create('\My\Custom\Baz');

// Create a "\My\Foo" object (using the map of aliases)
$factory->create('foo');

// Create a "\My\Custom\FooBar" object with the default resolver
$factory->create('my/custom/foo-bar');
```

Constructor options (class dependencies) are:

| Name               | Type       | Default    | Description                            |
| ------------------ | ---------- | ---------- | -------------------------------------- |
| `base_class`       | _string_   | `''`   | Optional. A base class (or interface) to ensure a type of object.
| `default_class`    | _string_   | `''`   | Optional. A default class, as fallback when the requested object is not resolvable.
| `arguments`        | _array_    | `[]`   | Optional. Constructor arguments that will be passed along to created instances.
| `callback`         | _Callable_ | `null` | Optional. A callback function that will be called upon object creation.
| `resolver`         | _Callable_ | `null`<sup>[1]</sup> | Optional. A class resolver. If none is provided, a default will be used.
| `resolver_options` | _array_    | `null` | Optional. Resolver options (prefix, suffix, capitals and replacements). This is ignored / unused if `resolver` is provided.

<small>[1] If no resolver is provided, a default `\Charcoal\Factory\GenericResolver` will be used.</small>

## The resolver

The _type_ (class identifier) sent to the `create()` method can be parsed / resolved with a custom `Callable` resolver.

If no `resolver` is passed to the constructor, a default `\Charcoal\Factory\GenericResolver` is used. This default resolver transforms, for example, `my/custom/foo-bar` into `\My\Custom\FooBar`.

## Class map / aliases

Class _aliases_ can be added by setting them in the Factory constructor:

```php
$factory = new GenericFactory([
    'map' => [
        'foo' => '\My\Foo',
        'bar' => '\My\Bar'
    ]
]);

// Create a `\My\Foo` instance
$obj = $factory->create('foo');
```

## Ensuring a type of object

Ensuring a type of object can be done by setting the `base_class` property.

The recommended way of setting the base class is by setting it in the constructor:

```php
$factory = new GenericFactory([
    'base_class' => '\My\Foo\BaseClassInterface'
]);
```

> 👉 Note that _Interfaces_ can also be used as a factory's base class.

## Setting a default type of object

It is possible to set a default type of object (default class) by setting the `default_class` property.

The recommended way of setting the default class is by setting it in the constructor:

```php
$factory = new GenericFactory([
    'default_class' => '\My\Foo\DefaultClassInterface'
]);
```

> ⚠ Setting a default class name changes the standard Factory behavior. When an invalid class name is used, instead of throwing an `Exception`, an object of the default class type will **always** be returned.

## Constructor arguments

It is possible to set "automatic" constructor arguments that will be passed to every created object.

The recommended way of setting constructor arguments is by passing an array of arguments to the constructor:

```php
$factory = new GenericFactory([
    'arguments' => [
        [
            'logger' => $container['logger']
        ],
        $secondArgument
    ]
]);
```

## Executing an object callback

It is possible to execute an object callback upon object instanciation. A callback is any `Callable` that takes the newly created object by reference as its function parameter.

```php
// $obj is the newly created object
function callback(&$obj);
```

The recommended way of adding an object callback is by passing a `Callable` to the constructor:

```php
$factory = new GenericFactory([
    'arguments' => [[
        'logger' => $container['logger']
    ]],
    'callback' => function (&$obj) {
        $obj->foo('bar');
        $obj->logger->debug('Objet instanciated from factory.');
    }
]);
```


# Development

To install the development environment:

```shell
★ composer install --prefer-source
```

To run the scripts (phplint, phpcs and phpunit):

```shell
★ composer test
```

## Development dependencies

-   `phpunit/phpunit`
-   `squizlabs/php_codesniffer`
-   `satooshi/php-coveralls`

## Continuous Integration

| Service | Badge | Description |
| ------- | ----- | ----------- |
| [Travis](https://travis-ci.org/locomotivemtl/charcoal-factory) | [![Build Status](https://travis-ci.org/locomotivemtl/charcoal-factory.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-factory) | Runs code sniff check and unit tests. Auto-generates API documentation. |
| [Scrutinizer](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-factory/) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-factory/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-factory/?branch=master) | Code quality checker. Also validates API documentation quality. |
| [Coveralls](https://coveralls.io/github/locomotivemtl/charcoal-factory) | [![Coverage Status](https://coveralls.io/repos/github/locomotivemtl/charcoal-factory/badge.svg?branch=master)](https://coveralls.io/github/locomotivemtl/charcoal-factory?branch=master) | Unit Tests code coverage. |
| [Sensiolabs](https://insight.sensiolabs.com/projects/0aec930b-d696-415a-b4ef-a15c1a56509e) | [![SensioLabsInsight](https://insight.sensiolabs.com/projects/0aec930b-d696-415a-b4ef-a15c1a56509e/mini.png)](https://insight.sensiolabs.com/projects/0aec930b-d696-415a-b4ef-a15c1a56509e) | Another code quality checker, focused on PHP. |

## Coding Style

All Charcoal modules follow the same coding style and `charcoal-factory` is no exception. For PHP:

-   [_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
-   [_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
-   [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), autoloading is therefore provided by _Composer_
-   [_phpDocumentor_](http://phpdoc.org/)
-   Read the [phpcs.xml](phpcs.xml) file for all the details on code style.

> Coding style validation / enforcement can be performed with `composer phpcs`. An auto-fixer is also available with `composer phpcbf`.

# Authors

-   Mathieu Ducharme <mat@locomotive.ca>

# License

Charcoal is licensed under the MIT license. See [LICENSE](LICENSE) for details.



## Report Issues

In case you are experiencing a bug or want to request a new feature head over to the [Charcoal monorepo issue tracker](https://github.com/charcoalphp/charcoal/issues)



## Contribute

The sources of this package are contained in the Charcoal monorepo. We welcome contributions for this package on [charcoalphp/charcoal](https://github.com/charcoalphp/charcoal).


# Changelog

### 0.3.2

-   Split resolved classname "cache" by factory class.

### 0.3.1

_Released 2016-03-22_

-   Keep resolved classname in memory. Can greatly speed things up if instancing many objects.

### 0.3

_Released 2016-01-28_

-   Add the `setArguments()` method to factories.
-   Add the `setCallback()` method to factories.
-   Execute the callback when using the default class too.

### 0.2

_Released 2016-01-26_

Minor (but BC-breaking) changes to Charcoal-Factory

-   Full PSR1 compliancy (All methods are now camel-case).
-   Add a callback argument to the `create()` method.
-   `create()` and `get()` are now `final` in the base abstract factory class.
-   Internal code, docs and tool improvements.

### 0.1

_Released 2015-11-25_

-   Initial release
