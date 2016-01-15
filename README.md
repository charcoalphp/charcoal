Charcoal Config
===============

Configuration container for all things Charcoal.

[![Build Status](https://travis-ci.org/locomotivemtl/charcoal-config.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-config)

This package provides easy hierarchical configuration container (for config storage and access).
`Charcoal\Config` acts as a configuration registry / repository.

## Main features

- [Load data from ini, json or php files.](#supported-file-formats)
- [Customizable separator access.](#separators)
- [Delegates (Chaining configurations).](#delegates)
- [Array access.](#array-access)
- [Implement Interop-Container.](#interoperability)
- [Provide Configurable Interface](#configurable)

## Supported file formats

There are currently 3 supported file formats: `ini`, `json` and `php`.

To load configuration from a file, simply use the `add_file()` method. The file's extension will be used to determine how to load the file.

It is also possible to load a config file directly from the constructor, by passing a file _string_ as the first argument.

``` php
$config = new \Charcoal\GenericConfig('../config/my-config.json');
```

### JSON configuration

For the JSON file:

```json
{
	"example":{
		"foo":"bar"
	}
}
```

Loading this file into configuration would be:

```php
$config = new \Charcoal\GenericConfig();
$config->add_file('./config/my-config.json');

// Output "bar"
echo $config['example/foo'];
```

If you want to load a configuration file *without* adding its content automatically, use:

```php
$config = new \Charcoal\GenericConfig();
$file_content = $config->load_file('my-config.json');
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
$config->add_file('./config/my-config.ini');

// Outputs "bar"
echo $config['exampe/foo'];
```

### PHP configuration

The PHP configuration is loaded from an internal `include`, therefore, the scope of `$this` in the php file is the current _Config_ instance.

For the PHP file:

```php
<?php
$this['example'] = [
	'foo'=>'bar'
];
```

Loading this file into configuration would be:

```php
$config = new \Charcoal\GenericConfig();
$config->add_file('./config/my-config.php');

// Outputs "bar"
echo $config['example/foo'];
```

## Separators

It is possible to fetch embedded _array-ish_ values recursively in a single call with the help of _separators_.

The default separator is `/` (it can be retrieved with `separator()`) but it can be changed easily with `set_separator()`.

> 👉 Separator must be a single character. An exception will be thrown if trying to call `set_separator()` with a longer string.

### How to use

```php
$config = new \Charcoal\GenericConfig();
$config->set_separator('.'); // Default is "/"
$config->merge([
	'foo', [
		'baz'=>example,
		'bar'=>42
	]
]);
// Ouput "42"
echo $config->get('foo.bar');
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

$config->add_delegate($config2);

// Returns 42
echo $config->get('bar');
```

Delegates can be set with:

- `set_delegates()` to set an array of delegates.
- `add_delegate()` to add a config object at the end of the delegate list.
- `prepend_delegate()` to add a config object at the beginning of the delegate list.

It is also possible to set delegates by passing them (as an array of ConfigInterface) to the constructor:

```php
$config = new \Charcoal\Config\GenericConfig('../config/my-config.json', [$delegate1, $delegate2]);
```

> 👉 The order of the delegates is important. They are looked in the order they are added, so the first match is returned. Use `prepend_delegate()` to add a config at the beginning of the stack (top priority).

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

The config object can be set with `set_config()` and retrieve with `config()`.

Implementation example:

```php
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

use \Acme\Foo\FooConfig;

class Foo implements ConfigurableInterface
{
	use ConfigurableTrait;

	public function create_config(array $data = null)
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
$foo->set_config([
	'bar'=>[
		'baz'=>42
	]
]);

// echo 42
$foo_config = $foo->config();
echo $foo_config['bar/baz'];
```

## Development

### Coding Style

All Charcoal modules follow the same coding style and `charcoal-core` is no exception. For PHP:

- [_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md), except for
	- Method names MUST be declared in `snake_case`.
- [_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md), except the PSR-1 requirement.
- [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), autoloading is therefore provided by _Composer_
- [_phpDocumentor_](http://phpdoc.org/)
	- Add DocBlocks for all classes, methods, and functions;
	- For type-hinting, use `boolean` (instead of `bool`), `integer` (instead of `int`), `float` (instead of `double` or `real`);
	- Omit the `@return` tag if the method does not return anything.
- Naming conventions
	- Use `snake_case`, not `camelCase`, for variable, option, parameter, argument, function, and method names;
	- Prefix abstract classes with `Abstract`;
	- Suffix interfaces with `Interface`;
	- Suffix traits with `Trait`;
	- Suffix exceptions with `Exception`;
	- For arrays, use short notation `[]` (instead of `array()`).

Coding styles are  enforced with `grunt phpcs` ([_PHP Code Sniffer_](https://github.com/squizlabs/PHP_CodeSniffer)). The actual ruleset can be found in `phpcs.xml`.

> 👉 To fix minor coding style problems, run `grunt phpcbf` ([_PHP Code Beautifier and Fixer_](https://github.com/squizlabs/PHP_CodeSniffer)). This tool uses the same ruleset as *phpcs* to automatically correct coding standard violations.

The main PHP structure follows the [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md) standard. Autoloading is therefore provided by _Composer_.

## Authors

- Mathieu Ducharme <mat@locomotive.ca>

## Changelog

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

- Initial release of `charcoal-config`,
