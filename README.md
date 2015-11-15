Charcoal Factory
================

`Charcoal\Factory` creates factories which create or build dynamic Charcoal objects.

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


#Factories

## About factories

A factory object can do 2 things:

- `build` an object, from an array of options.
- `create` an object, from a class ident.

There are 3 default type of factory provided:

### `ClassMapFactory`
Get the **class name** from an associative array with the **class ident** key.

### `ClassNameFactory`
Use the **class ident** directly for **class name**.

### `IdentFactory`
 Generate a **class name** (`\Namespace\Class\Name`) from a **class ident** (`namespace/class/ident`).

## Ensuring a type of object
Ensuring a type of object can be done by setting the `base_class`, either forced in a class:

```php
class MyFactory
{
	public function base_class()
	{
		return '\My\Foo\BaseClassInterface`;
	}
}
```

Or, dynamically:

```php
$factory = IdentFactory::instance();
$factory->set_base_class('\My\Foo\BaseClassInterface');
```	

## Setting a default type of object

It is possible to set a default type of object (default class) by setting the `default_class`, either forced in a class:

```php
class MyFactory
{
	public function default_class()
	{
		return '\My\Foo\DefaultClassInterface`;
	}
}
```

Or, dynamically:

```php
$factory = IdentFactory::instance();
$factory->set_default_class('\My\Foo\DefaultClassInterface');
```	

> ⚠ Setting a default class name changes the standard Factory behavior. When an invalid class name is used, instead of throwing an `Exception`, an object of the default class type will **always** be returned.


## The `AbstractFactory` API

| Method     | Parameters | Return value | Description |
| ---------- | ---------- | ------------ | ----------- |
| `build`    | _array_ `$data` | _Object_ | Build a class from an array of options.
| `create`   | _string_ `$type` [, _array_ `$constructor_args`] | _Object_ | Create a class from a "type" string.
| `get`      | _string_ `$type` | _Object_
| `set_base_class` | 
| `base_class` |
| `set_default_class` |
| `default_class` |
| `classname` | _string_ `$type` | _string_ Class name | **abstract**, must be reimplemented in children classes.
| `validate`  | _string_ `$type` | _boolean_ | **abstract**, must be reimplemented in children classes.

### The `ClassMapFactory` additional API

### The `ClassNameFactory` additional API
Because the `ClassNameFactory` uses the parameter directly 

# Usage

To create a `\Foo\Bar\Baz` object:

```php
$class_ident = '/foo/bar/baz';
$obj = IdentFactory::instance()->create($class_ident);
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
