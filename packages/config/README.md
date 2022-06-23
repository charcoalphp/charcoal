Charcoal Config
===============

[![License][badge-license]][charcoal-config]
[![Latest Stable Version][badge-version]][charcoal-config]
[![Code Quality][badge-scrutinizer]][dev-scrutinizer]
[![Coverage Status][badge-coveralls]][dev-coveralls]
[![SensioLabs Insight][badge-sensiolabs]][dev-sensiolabs]
[![Build Status][badge-travis]][dev-travis]

A Charcoal component for organizing configuration data and designing object data models.

This component is the glue for much of the [Charcoal framework][charcoal-app].



## Table of Contents

- [Installation](#installation)
    -   [Requirements](#requirements)
- [Entity & Config](#entity--config)
    -   [Entity](#entity)
    -   [Config](#config)
- [Features](#features)
    -   [File Loader](#file-loader)
    -   [Key Separator Lookup](#key-separator-lookup)
    -   [Delegated Data Lookup](#delegates-lookup)
    -   [Array Access](#array-access)
    -   [Interoperability](#interoperability)
    -   [Configurable objects](#configurable-objects)
- [Development](#development)
    -   [API Documentation](#api-documentation)
    -   [Development Dependencies](#development-dependencies)
    -   [Coding Style](#coding-style)
- [Credits](#credits)
- [License](#license)
- [Report Issues](#report-issues)
- [Contribute](#contribute)



## Installation

The preferred (and only supported) method is with Composer:

```shell
$ composer require charcoal/config
```

### Requirements

-   [**PHP 5.6+**](https://php.net): _PHP 7_ is recommended.

#### PSR

-   [**PSR-11**][psr-11]: Common interface for dependency containers. For [interoperable configsets](#interoperability).



## Entity & Config

The Config component simplifies access to object data. It provides a property-based user interface for retrieving and storing arbitrary data within application code. Data is organized into two primary object types: _Entity_ and _Config_.

### Entity

Entities represent simple data-object containers designed as a flexible foundation for domain model objects.  
Examples: a single result from a repository or serve as the basis for each component of an MVC system.

-   **Class**: [`Charcoal\Config\AbstractEntity`](src/Charcoal/Config/AbstractEntity.php)
-   **Methods**: `keys`, `data`, `setData`, `has`, `get`, `set`
-   **Interface**: [`Charcoal\Config\EntityInterface`](src/Charcoal/Config/EntityInterface.php)
    -   `ArrayAccess`
    -   `JsonSerializable`
    -   `Serializable`

### Config

Configs are advanced _Entities_ designed for runtime configuration values with support for loading files and storing hierarchical data.  
Examples: application preferences, service options, and factory settings.

-   **Class**: [`Charcoal\Config\AbstractConfig`](src/Charcoal/Config/AbstractConfig.php)
    -   `IteratorAggregate`
    -   `Psr\Container\ContainerInterface`
-   **Methods**: `defaults`, `merge`, `addFile`
-   **Interface**: [`Charcoal\Config\ConfigInterface`](src/Charcoal/Config/ConfigInterface.php)
    -   `Charcoal\Config\EntityInterface`
    -   `Charcoal\Config\FileAwareInterface`
    -   `Charcoal\Config\SeparatorAwareInterface`
    -   `Charcoal\Config\DelegatesAwareInterface`



## Features

-   [Read data from INI, JSON, PHP, and YAML files](#file-loader)
-   [Customizable separator for nested lookup](#key-separator-lookup)
-   [Share configuration entries](#delegates-lookup)
-   [Array accessible entities](#array-access)
-   [Interoperable datasets](#interoperability)
-   [Configurable objects](#configurable-objects)



### File Loader

The _Config_ container currently supports four file formats: INI, JSON, PHP, and YAML.

A configuration file can be imported into a Config object via the `addFile($path)` method, or by direct instantiation:

```php
use Charcoal\Config\GenericConfig as Config;

$cfg = new Config('config.json');
$cfg->addFile('config.yml');
```

The file's extension will be used to determine how to import the file.
The file will be parsed and, if its an array, will be merged into the container.

If you want to load a configuration file _without_ adding its content to the Config, use `loadFile($path)` instead.
The file will be parsed and returned regardless if its an array.

```php
$data = $cfg->loadFile('config.php');
```

Check out the [documentation](docs/file-loader.md) and [examples](tests/Charcoal/Config/Fixture/pass) for more information.



### Key Separator Lookup

It is possible to lookup, retrieve, assign, or merge values in multi-dimensional arrays using _key separators_.

In Config objects, the default separator is the period character (`.`). The token can be retrieved with the `separator()` method and customized using the `setSeparator()` method.

```php
use Charcoal\Config\GenericConfig as Config;

$cfg = new Config();
$cfg->setSeparator('/');
$cfg->setData([
    'database' => [
        'params' => [
            'name' => 'mydb',
            'user' => 'myname',
            'pass' => 'secret',
        ]
    ]
]);

echo $cfg['database/params/name']; // "mydb"
```

Check out the [documentation](docs/separator-lookup.md) for more information.



### Delegates Lookup

Delegates allow several objects to share values and act as fallbacks when the current object cannot resolve a given data key.

In Config objects, _delegate objects_ are regsitered to an internal stack. If a data key cannot be resolved, the Config iterates over each delegate in the stack and stops on
the first match containing a value that is not `NULL`.

```php
use Charcoal\Config\GenericConfig as Config;

$cfg = new Config([
    'driver' => null,
    'host'   => 'localhost',
]);
$delegate = new Config([
    'driver' => 'pdo_mysql',
    'host'   => 'example.com',
    'port'   => 11211,
]);

$cfg->addDelegate($delegate);

echo $cfg['driver']; // "pdo_mysql"
echo $cfg['host']; // "localhost"
echo $cfg['port']; // 11211
```

Check out the [documentation](docs/delegates-lookup.md) for more information.



### Array Access

The Entity object implements the `ArrayAccess` interface and therefore can be used with array style:

```php
$cfg = new \Charcoal\Config\GenericConfig();

// Assigns a value to "foobar"
$cfg['foobar'] = 42;

// Returns 42
echo $cfg['foobar'];

// Returns TRUE
isset($cfg['foobar']);

// Returns FALSE
isset($cfg['xyzzy']);

// Invalidates the "foobar" key
unset($cfg['foobar']);
```

> 👉 A data key MUST be a string otherwise `InvalidArgumentException` is thrown.



### Interoperability

The Config object implements [PSR-11](psr-11): `Psr\Container\ContainerInterface`.

This interface exposes two methods: `get()` and `has()`. These methods are implemented by the Entity object as aliases of `ArrayAccess::offsetGet()` and `ArrayAccess::offsetExists()`.

```php
$config = new \Charcoal\Config\GenericConfig([
    'foobar' => 42
]);

// Returns 42
$config->get('foobar');

// Returns TRUE
$config->has('foobar');

// Returns FALSE
$config->has('xyzzy');
```

> 👉 A call to the `get()` method with a non-existing key DOES NOT throw an exception.



### Configurable Objects

Also provided in this package is a _Configurable_ mixin:

-   `Charcoal\Config\ConfigrableInterface`
-   `Charcoal\Config\ConfigurableTrait`

Configurable objects (which could have been called "_Config Aware_") can have an associated Config object that can help define various properties, states, or other.

The Config object can be assigned with `setConfig()` and retrieved with `config()`.

An added benefit of `ConfigurableTrait` is the `createConfig($data)` method which is used to create a Config object if one is not assigned. This method can be overridden in sub-classes to customize the instance returned and whatever initial state might be needed.

Check out the [documentation](docs/configurable-objects.md) for examples and more information.



## Development

To install the development environment:

```shell
$ composer install
```

To run the scripts (phplint, phpcs, and phpunit):

```shell
$ composer test
```



### API Documentation

-   The auto-generated `phpDocumentor` API documentation is available at:  
    [https://locomotivemtl.github.io/charcoal-config/docs/master/](https://locomotivemtl.github.io/charcoal-config/docs/master/)
-   The auto-generated `apigen` API documentation is available at:  
    [https://codedoc.pub/charcoalconfig/master/](https://codedoc.pub/charcoal/config/master/index.html)



### Development Dependencies

-   [php-coveralls/php-coveralls][phpcov]
-   [phpunit/phpunit][phpunit]
-   [squizlabs/php_codesniffer][phpcs]



### Coding Style

The charcoal-config module follows the Charcoal coding-style:

-   [_PSR-1_][psr-1]
-   [_PSR-2_][psr-2]
-   [_PSR-4_][psr-4], autoloading is therefore provided by _Composer_.
-   [_phpDocumentor_](http://phpdoc.org/) comments.
-   [phpcs.xml.dist](phpcs.xml.dist) and [.editorconfig](.editorconfig) for coding standards.

> Coding style validation / enforcement can be performed with `composer phpcs`. An auto-fixer is also available with `composer phpcbf`.

> This module should also throw no error when running `phpstan analyse -l7 src/` 👍.



## Credits

-   [Mathieu Ducharme](https://github.com/mducharme)
-   [Locomotive](https://locomotive.ca/)



## License

Charcoal is licensed under the MIT license. See [LICENSE](LICENSE) for details.



## Report Issues

In case you are experiencing a bug or want to request a new feature head over to the [Charcoal monorepo issue tracker](https://github.com/charcoalphp/charcoal/issues)



## Contribute

The sources of this package are contained in the Charcoal monorepo. We welcome contributions for this package on [charcoalphp/charcoal](https://github.com/charcoalphp/charcoal).


[charcoal-app]:       https://packagist.org/packages/charcoal/app
[charcoal-config]:    https://packagist.org/packages/charcoal/config

[phpunit]:            https://packagist.org/packages/phpunit/phpunit
[phpcs]:              https://packagist.org/packages/squizlabs/php_codesniffer
[phpcov]:             https://packagist.org/packages/php-coveralls/php-coveralls

[dev-scrutinizer]:    https://scrutinizer-ci.com/g/locomotivemtl/charcoal-config/
[dev-coveralls]:      https://coveralls.io/r/locomotivemtl/charcoal-config
[dev-sensiolabs]:     https://insight.sensiolabs.com/projects/27ad205f-4208-4fa6-9dcf-534b3a1c0aaa
[dev-travis]:         https://travis-ci.org/locomotivemtl/charcoal-config

[badge-license]:      https://img.shields.io/packagist/l/charcoal/config.svg?style=flat-square
[badge-version]:      https://img.shields.io/packagist/v/charcoal/config.svg?style=flat-square
[badge-scrutinizer]:  https://img.shields.io/scrutinizer/g/locomotivemtl/charcoal-config.svg?style=flat-square
[badge-coveralls]:    https://img.shields.io/coveralls/locomotivemtl/charcoal-config.svg?style=flat-square
[badge-sensiolabs]:   https://img.shields.io/sensiolabs/i/27ad205f-4208-4fa6-9dcf-534b3a1c0aaa.svg?style=flat-square
[badge-travis]:       https://img.shields.io/travis/locomotivemtl/charcoal-config.svg?style=flat-square

[psr-1]:  https://www.php-fig.org/psr/psr-1/
[psr-2]:  https://www.php-fig.org/psr/psr-2/
[psr-4]:  https://www.php-fig.org/psr/psr-4/
[psr-11]: https://www.php-fig.org/psr/psr-11/
