Charcoal Config
===============

The Config package provides abstract tools for organizing configuration data and designing object data models.

## Installation

```shell
composer require charcoal/config
```

## Overview

### Entity & Config

The Config component simplifies access to object data. It provides a property-based user interface for retrieving and storing arbitrary data within application code. Data is organized into two primary object types: _Entity_ and _Config_.

#### Entity

Entities represent simple data-object containers designed as a flexible foundation for domain model objects.  
Examples: a single result from a repository or serve as the basis for each component of an MVC system.

* **Class**: [`Charcoal\Config\AbstractEntity`](src/Charcoal/Config/AbstractEntity.php)
* **Methods**: `keys`, `data`, `setData`, `has`, `get`, `set`
* **Interface**: [`Charcoal\Config\EntityInterface`](src/Charcoal/Config/EntityInterface.php)
  * `ArrayAccess`
  * `JsonSerializable`
  * `Serializable`

#### Config

Configs are advanced _Entities_ designed for runtime configuration values with support for loading files and storing hierarchical data.  
Examples: application preferences, service options, and factory settings.

* **Class**: [`Charcoal\Config\AbstractConfig`](src/Charcoal/Config/AbstractConfig.php)
  * `IteratorAggregate`
  * `Psr\Container\ContainerInterface`
* **Methods**: `defaults`, `merge`, `addFile`
* **Interface**: [`Charcoal\Config\ConfigInterface`](src/Charcoal/Config/ConfigInterface.php)
  * `Charcoal\Config\EntityInterface`
  * `Charcoal\Config\FileAwareInterface`
  * `Charcoal\Config\SeparatorAwareInterface`
  * `Charcoal\Config\DelegatesAwareInterface`

### Features

* [Read data from INI, JSON, PHP, and YAML files](#file-loader)
* [Customizable separator for nested lookup](#key-separator-lookup)
* [Share configuration entries](#delegates-lookup)
* [Array accessible entities](#array-access)
* [Interoperable datasets](#interoperability)
* [Configurable objects](#configurable-objects)

#### File Loader

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

#### Key Separator Lookup

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

#### Delegates Lookup

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

#### Array Access

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

#### Interoperability

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

#### Configurable Objects

Also provided in this package is a _Configurable_ mixin:

* `Charcoal\Config\ConfigrableInterface`
* `Charcoal\Config\ConfigurableTrait`

Configurable objects (which could have been called "_Config Aware_") can have an associated Config object that can help define various properties, states, or other.

The Config object can be assigned with `setConfig()` and retrieved with `config()`.

An added benefit of `ConfigurableTrait` is the `createConfig($data)` method which is used to create a Config object if one is not assigned. This method can be overridden in sub-classes to customize the instance returned and whatever initial state might be needed.

Check out the [documentation](docs/configurable-objects.md) for examples and more information.

## Resources

* [Contributing](https://github.com/charcoalphp/.github/blob/main/CONTRIBUTING.md)
* [Report issues](https://github.com/charcoalphp/charcoal/issues) and
  [send pull requests](https://github.com/charcoalphp/charcoal/pulls)
  in the [main Charcoal repository](https://github.com/charcoalphp/charcoal)
