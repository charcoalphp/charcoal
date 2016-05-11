Charcoal Config
===============

Configuration container for all things Charcoal.

[![Build Status](https://travis-ci.org/locomotivemtl/charcoal-config.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-config)

This package provides easy hierarchical configuration container (for config storage and access).
`Charcoal\Config` acts as a configuration registry / repository.

## Main features

- [Load data from ini, json, php or yaml files.](#supported-file-formats)
- [Customizable separator access.](#separators)
- [Delegates (Chaining configurations).](#delegates)
- [Array access.](#array-access)
- [Implement Interop-Container.](#interoperability)
- [Provide Configurable Interface](#configurable)

## Supported file formats

There are currently 4 supported file formats: `ini`, `json`, `php` and `yaml`.

To load configuration from a file, simply use the `addFile($filename)` method. The file's extension will be used to determine how to load the file.

```php
$config = new \Charcoal\GenericConfig();
$config->addFile('./config/my-config.ext');
```

>If you want to load a configuration file *without* adding its content automatically, use `loadFile($filename)`:
>
> ```php
> $config = new \Charcoal\GenericConfig();
> $file_content = $config->loadFile('my-config.ext');
> ```

It is also possible to load a config file directly from the constructor, by passing a file _string_ as the first argument.

``` php
$config = new \Charcoal\GenericConfig('../config/my-config.json');
```

### JSON configuration

For the JSON file (ex: `config/my-config.json`):

```json
{
	"example":{
		"foo":"bar"
	}
}
```

Loading this file into configuration is done by using `addFile($filename)`:

```php
$config = new \Charcoal\GenericConfig();
$config->addFile('./config/my-config.json');

// Output "bar"
echo $config['example.foo'];
```

### INI configuration

For the INI file:

```ini
[example]
foo=bar
```

Loading this file into configuration would be:

```php
$config = new \Charcoal\GenericConfig();
$config->addFile('./config/my-config.ini');

// Outputs "bar"
echo $config['example.foo'];
```

### PHP configuration

The PHP configuration is loaded from an internal `include`, therefore, the scope of `$this` in the php file is the current _Config_ instance.

For the PHP file:

```php
$this['example'] = [
	'foo'=>'bar'
];
```

Loading this file into configuration would be:

```php
$config = new \Charcoal\GenericConfig();
$config->addFile('./config/my-config.php');

// Outputs "bar"
echo $config['example.foo'];
```

> Because `$this` references the actual `ConfigInterface` object, it is possible to include more config files from a PHP file:
>
> ```php
> <?php
> $this->addFile('./config/more-config.json');
> ```
>
> The recommended way of use a _Config_ object is to include a single `config/config.php` file (that is outside of the document root) that takes care of loading required configuration (json or PHP) files.

### Yaml configuration

To be able to use the yaml loader, make sure `symfony/yaml` is included in your project composer dependencies:

```shell
$ composer require symfony/yaml
```

For the YAML file (ex: `config/my-config.yml`):

```yaml
example:
	foo: bar
```

Loading this file into configuration would be:

```php
$config = new \Charcoal\GenericConfig();
$config->addFile('./config/my-config.yml');

// Outputs "bar"
echo $config['exampe.foo'];
```
> YAML files can have 2 different extensions: `.yml` or `.yaml`. The standard is to use `.yml`.

## Separators

It is possible to fetch embedded _array-ish_ values recursively in a single call with the help of _separators_.

The default separator is `.` (it can be retrieved with `separator()`) but it can be changed easily with `setSeparator()`.

> 👉 Separator must be a single character. An exception will be thrown if trying to call `setSeparator()` with a longer string.

### How to use

```php
$config = new \Charcoal\GenericConfig();
$config->setSeparator('/'); // Default is "."
$config->merge([
	'foo', [
		'baz'=>example,
		'bar'=>42
	]
]);
// Ouput "42"
echo $config->get('foo/bar');
```

## Delegates

It is possible to "chain" configuration containers with the help of _delegates_.

If one or more delegates are added to a class, they will be used as _fallback_ when trying to fetch a key that isn't set in the config.

```php
$config = new \Charcoal\Config\GenericConfig([
	'foo' => 'baz'
]);

// Returns `false`
$config->has('bar');

// Throws exception
echo $config->get('bar');

$config2 = new \Charcoal\Config\GenericConfig([
	'bar' => 42
]);

$config->addDelegate($config2);

// Returns 42
echo $config->get('bar');
```

Delegates can be set with:

- `setDelegates()` to set an array of delegates.
- `addDelegate()` to add a config object at the end of the delegate list.
- `prependDelegate()` to add a config object at the beginning of the delegate list.

It is also possible to set delegates by passing them (as an array of ConfigInterface) to the constructor:

```php
$config = new \Charcoal\Config\GenericConfig('../config/my-config.json', [$delegate1, $delegate2]);
```

> 👉 The order of the delegates is important. They are looked in the order they are added, so the first match is returned. Use `prependDelegate()` to add a config at the beginning of the stack (top priority).

## Array Access

The config class implements the `ArrayAccess` interface and therefore can be used with array style:

```php
$config = new \Charcoal\Config\GenericConfig();

// Set value with array
$config['foobar'] = 42;

// Returns `42`
echo $config['foobar'];

// Returns `true`
isset($config['foobar']);

// Returns `false`
isset($config['invalid-key']);

// Invalidate the "foobar" config key
unset($config['foobar']);
```

## Interoperability

The `\Charcoal\Config` container implements the `container-interop` interface.

See [https://github.com/container-interop/container-interop](https://github.com/container-interop/container-interop).

This interface requires the `get()` and `has()` methods:

```php
$config = new \Charcoal\Config\GenericConfig([
	'foobar'=>42
]);

// Returns `true`
$config->has('foobar');

// Returns `false`
$config->has('invalid-key');

// Returns `42`
$config->get('foobar');
```

## Configurable

Also provided in this package is a _Configurable_ interface (`\Charcoal\Config\ConfigrableInterface`) and its full implementation as a trait. `\Charcoal\Config\ConfigurableTrait`.

Configurable (which could have been called "_Config Aware_") objects can have an associated config instance that can help defines various properties, states, or other.

The config object can be set with `setConfig()` and retrieve with `config()`.

Note that the `ConfigurableTrait` adds an abstract method that must be implemented: `createConfig(array $data)` (returns `ConfigInterface`).

Implementation example:

```php
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

use \Acme\Foo\FooConfig;

class Foo implements ConfigurableInterface
{
	use ConfigurableTrait;

	public function createConfig(array $data = null)
	{
		$config = new FooConfig();
		if ($data !== null) {
			$config->merge($data);
		}
		return $config;
	}
}
```

The previous class could be use as such:

```php
$foo = new Foo();
$foo->setConfig([
	'bar'=>[
		'baz'=>42
	]
]);

// echo 42
$foo_config = $foo->config();
echo $foo_config['bar.baz'];

// Also echo 42
echo $foo->config('bar.baz');
```

# Development

To install the development environment:

```shell
$ composer install --prefer-source
```

## API documentation

- The auto-generated `phpDocumentor` API documentation is available at [https://locomotivemtl.github.io/charcoal-config/docs/master/](https://locomotivemtl.github.io/charcoal-config/docs/master/)
- The auto-generated `apigen` API documentation is available at [https://codedoc.pub/locomotivemtl/charcoal-config/master/](https://codedoc.pub/locomotivemtl/charcoal-config/master/index.html)

## Development dependencies

- `phpunit/phpunit`
- `squizlabs/php_codesniffer`
- `satooshi/php-coveralls`

## Continuous Integration

| Service | Badge | Description |
| ------- | ----- | ----------- |
| [Travis](https://travis-ci.org/locomotivemtl/charcoal-config) | [![Build Status](https://travis-ci.org/locomotivemtl/charcoal-config.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-config) | Runs code sniff check and unit tests. Auto-generates API documentation. |
| [Scrutinizer](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-config/) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-config/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-config/?branch=master) | Code quality checker. Also validates API documentation quality. |
| [Coveralls](https://coveralls.io/github/locomotivemtl/charcoal-config) | [![Coverage Status](https://coveralls.io/repos/github/locomotivemtl/charcoal-config/badge.svg?branch=master)](https://coveralls.io/github/locomotivemtl/charcoal-config?branch=master) | Unit Tests code coverage. |
| [Sensiolabs](https://insight.sensiolabs.com/projects/27ad205f-4208-4fa6-9dcf-534b3a1c0aaa) | [![SensioLabsInsight](https://insight.sensiolabs.com/projects/27ad205f-4208-4fa6-9dcf-534b3a1c0aaa/mini.png)](https://insight.sensiolabs.com/projects/27ad205f-4208-4fa6-9dcf-534b3a1c0aaa) | Another code quality checker, focused on PHP. |

## Coding Style

The Charcoal-Config module follows the Charcoal coding-style:

- [_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
- [_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
- [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), autoloading is therefore provided by _Composer_.
- [_phpDocumentor_](http://phpdoc.org/) comments.
- Read the [phpcs.xml](phpcs.xml) file for all the details on code style.

> Coding style validation / enforcement can be performed with `composer phpcs`. An auto-fixer is also available with `composer phpcbf`.

## Authors

- Mathieu Ducharme <mat@locomotive.ca>

## Changelog

### 0.6
_Released on 2016-05-10_

- Support for Yaml files.

### 0.5.1
_Released on 2016-05-09_

- Minor internal changes.

### 0.5
_Released on 2016-02-02_

- Split the base config class into AbstractEntity.
- AbstractEntity is the default data container that implements ArrayAccess, Container Interface and serialization.

### 0.4
_Released on 2016-01-16_

This release breaks compatibility.

- Move to camelCase, for 100% PSR-1 compliance.

### 0.3
_Released on 2016-01-15_

This releases breaks compatibility

- AbstractConfig constructor is now final.
- `set_data()` has been renamed to `merge()`.
- `merge()` (previously set_data) can now accept any `Traversable` objects, as well as array.
- `default_data()` has been renamed to `defaults()`
- Added a new `load_file()` method, to return the content of a config file.
- Added the `keys()` method, to retrieve the list of keys of the config file.
- Added the `data()` method, to retrieve the config as an array data, now that we have `keys()`.
- Config now inherits `IteratorAggregate` / `Traversable` (made possible with `data()`).
- Config is now `serializable` AND `jsonSerializable`.
- Setter rules can be overridden in children classes (for PSR2-style setter, for example).
- ConfigurableInterface / Trait `config()` method now accepts an optional `$key` argument.

### 0.2
_Released on 2015-12-09_

- Added the "delegates" feature.
- Setting value with a separator now tries to set as array.
- Implements the container-interop interface.

### 0.1.1
_Released on 2015-12-02_

- Removed the second argument for the constructor (currently unused).
- Clearer error message on invalid JSON files.
- Fix composer.json and the autoloader.
- Various internal changes (PSR2 compliancy, _with psr1 exception_).

### 0.1
_Released on 2015-08-25_

- Initial release of `charcoal-config`

# License

**The MIT License (MIT)**

_Copyright © 2016 Locomotive inc._
> See [Authors](#authors).

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
