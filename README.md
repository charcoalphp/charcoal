Charcoal Factory
================

`Charcoal\Factory` defines _factories_, which create or build dynamic Charcoal PHP objects.

[![Build Status](https://travis-ci.org/locomotivemtl/charcoal-factory.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-factory)

# How to install
The preferred (and only supported) way of installing _charcoal-factory_ is with **composer**:

```shell
$ composer require locomotivemtl/charcoal-factory
```

## Dependencies

- [`PHP 5.5+`](http://php.net)
	- Older versions of PHP are deprecated, therefore not supported for charcoal-app.

> 👉 Development dependencies, which are optional when using charcoal-app, are described in the [Development](#development) section of this README file.


# Factories

## About factories

Factories have only one purpose: to create / instanciate new PHP objects. There is 2 different methods of object creation:

- `build` an object, from an array of options.
- `create` an object, from a class ident.

Additionnally, the `get` method can be called to retrieve the last created instance.

```php
$factory = new \Charcoal\Factory\IdentFactory();

// Ensure the created object is a Charcoal Model
$factory->set_base_class('\Charcoal\Model\ModelInterface');

// Create a "news" object (from the `charcoal-cms` module)
$factory->create('charcoal/cms/news');



```

There are 3 default type of factory provided:

### `GenericFactory`
Resolve the **class name** by using the requested **type** directly as the class name.

### `MapFactory`
Resolve the **class name** from an associative array (_map_) with the requested **type** as key.


### `ResolverFactory`
Resolves the **class name** from the `resolve()` method, which typically transform the requested **type** by:

## Ensuring a type of object
Ensuring a type of object can be done by setting the `base_class`, either forced in a class:

```php
class MyFactory extends AbstractFactory
{
	public function base_class()
	{
		return '\My\Foo\BaseClassInterface`;
	}
}
```

Or, dynamically:

```php
$factory = ResolverFactory::instance();
$factory->set_base_class('\My\Foo\BaseClassInterface');
```	

> 👉 Note that _Interfaces_ can also be used as a factory's base class.

## Setting a default type of object

It is possible to set a default type of object (default class) by setting the `default_class`, either forced in a class:

```php
class MyFactory extends AbstractFactory
{
	public function default_class()
	{
		return '\My\Foo\DefaultClassInterface`;
	}
}
```

Or, dynamically:

```php
$factory = ResolverFactory::instance();
$factory->set_default_class('\My\Foo\DefaultClassInterface');
```	

> ⚠ Setting a default class name changes the standard Factory behavior. When an invalid class name is used, instead of throwing an `Exception`, an object of the default class type will **always** be returned.


## The `AbstractFactory` API

| Method     | Return value | Description |
| ---------- | ---------- | ------------ | ----------- |
| `build(array $data)` | _Object_ | Build a class from an array of options.
| `create(string $type)` [, _array_ `$constructor_args`] | _Object_ | Create a class from a "type" string.
| `get(_string $type)` | _Object_ | Get returns the latest created class instance, or a new one if none exists.
| `set_base_class(string $classname)` | 
| `base_class()` |
| `set_default_class(string $classname)` |
| `default_class` |
| `resolve(string $type)` | _string_ Class name | **abstract**, must be reimplemented in children classes.
| `is_resolvable(string $type)` | _boolean_ | **abstract**, must be reimplemented in children classes.

### The `MapFactory` additional API

| Method     | Return value | Description |
| ---------- | ---------- | ------------ | ----------- |
| `add_class($type, $class_name)` | _Chainable_ | 
| `set_map(array $map)` | _Chainable_ |
| `map()` | _array_ | 

### The `GenericFactory` additional API
Because the `ClassNameFactory` uses the parameter directly, there is no additional methods for this type of class.

The `resolve()` method simply returns its _type_ argument, and the `validate()` method simply ensures its _type_ argument is a valid (existing) class.

###The `ResolverFactory` additional API
The `ResolverFactory` resolves the classname from the class resolver options:

- `resolver_prefix` _string_ that will be prepended to the resolved class name.
- `resolver_suffix` _string_ that will be appended to the resolved class name.
- `resolver_capitals` _array_ of characters that will cause the next character to be capitalized.
- `resolver_replacements` _array_

| Method     | Return value | Description |
| ---------- | ---------- | ------------ | ----------- |
| `set_resolver_prefix(string $prefix)` | _Chainable_ |
| `resolver_prefix()` | _string_ | 
| `set_resolver_suffix(string $suffix)` | _Chainable_ |
| `resolver_suffix()` | _string_ |
| `set_resolver_capitals(array $capitals)` | _Chainable_ | 
| `resolver_capitals()` | _array_ | 
| `set_resolver_replacements(array $replacements)` | _Chainable_ | 
| `resolver_replacements()` | _array_ | 

# Usage

To create a `\Foo\Bar\Baz` object:

```php
use \Charcoal\Factory\ResolverFactory;
$obj = ResolverFactory::instance()->create('foo/bar/baz');
```

# Development

To install the development environment:

```shell
$ npm install
$ composer install
```

## Development dependencies

- `npm`
- `grunt` (install with `npm install grunt-cli`)
- `composer`
- `phpunit`

## Coding Style

The Charcoal-App module follows the Charcoal coding-style:

- [_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md), except for
  - Method names MUST be declared in `snake_case`.
- [_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md), except for the PSR-1 requirement.
- [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), autoloading is therefore provided by _Composer_
- [_phpDocumentor_](http://phpdoc.org/)
  - Add DocBlocks for all classes, methods, and functions;
  - For type-hinting, use `boolean` (instead of `bool`), `integer` (instead of `int`), `float` (instead of `double` or `real`);
  - Omit the `@return` tag if the method does not return anything.
- Naming conventions
  - Read the [phpcs.xml](phpcs.xml) file for all the details.

> 👉 Coding style validation / enforcement can be performed with `grunt phpcs`. An auto-fixer is also available with `grunt phpcbf`.

## Authors

- Mathieu Ducharme <mat@locomotive.ca>

## Changelog

### 0.1
_Unreleased_
- Initial release
