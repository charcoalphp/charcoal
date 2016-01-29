Charcoal Factory
================

Factories create or build dynamic PHP objects.

[![Build Status](https://travis-ci.org/locomotivemtl/charcoal-factory.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-factory)

# Table of contents

- How to install
	- Dependencies
- Factories
	- About factories
	- Ensuring a type of object
	- Setting a default type of object
	- Constructor arguments
	- Executing an object callback

# How to install

The preferred (and only supported) way of installing _charcoal-factory_ is with **composer**:

```shell
$ composer require locomotivemtl/charcoal-factory
```

## Dependencies

- [`PHP 5.5+`](http://php.net)
	- Older versions of PHP are deprecated, therefore not supported for charcoal-factory.

> 👉 Development dependencies, which are optional when using charcoal-factory, are described in the [Development](#development) section of this README file.


# Factories

## About factories

Factories have only one purpose: to create / instanciate new PHP objects. There is 2 different methods of object creation:

- Typically, the `create` method instantiates an object, from a class ident.
- Additionnally, the `get` method can be called to retrieve the last created instance.

```php
$factory = new \Charcoal\Factory\ResolverFactory();

// Ensure the created object is a Charcoal Model
$factory->setBaseClass('\Charcoal\Model\ModelInterface');

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

Ensuring a type of object can be done by setting the `baseClass`, either forced in a class:

```php
class MyFactory extends AbstractFactory
{
	public function baseClass()
	{
		return '\My\Foo\BaseClassInterface`;
	}
}
```

Or, dynamically, with `setBaseClass()`:

```php
$factory = new ResolverFactory();
$factory->setBaseClass('\My\Foo\BaseClassInterface');
```

> 👉 Note that _Interfaces_ can also be used as a factory's base class.

## Setting a default type of object

It is possible to set a default type of object (default class) by setting the `defaultClass`, either forced in a class:

```php
class MyFactory extends AbstractFactory
{
	public function defaultClass()
	{
		return '\My\Foo\DefaultClassInterface`;
	}
}
```

Or, dynamically, with `setDefaultClass()`:

```php
$factory = new ResolverFactory();
$factory->setDefaultClass('\My\Foo\DefaultClassInterface');
```

> ⚠ Setting a default class name changes the standard Factory behavior. When an invalid class name is used, instead of throwing an `Exception`, an object of the default class type will **always** be returned.

## Constructor arguments

It is possible to set "automatic" constructor arguments that will be passed to every created object.

The easiest way to achieve this is by passing the arguments as the 2nd parameter of a factory's `create()` method.

```php
$factory->create('foo/bar', [$args]);
```

Another way of providing constructor arguments to a factory is with the `setArguments()` method. Assume that the `\Foo\Bar` object have the following constructor:

```php
namespace \Foo;

class Bar {
	public function __constructor($dependencies)
	{
		$this->setFooDependency($dependencies['foo']);
		$this->setBar($dependencies['bar']);
	}
}
```

Then the following code will create an object with proper constructor arguments.

```
$factory = new \Charcoal\Factory\MapFactory();
$factory->setMap([
	'obj' => '\Foo\Bar'
]);
$factory->setArguments([
	'foo'=>new Dependency1(),
	'bar'=>new Dependency2()
]);
$obj = $factory->create('obj');
```

## Executing an object callback

It is possible to execute an object callback upon object instanciation by passing a `callable` object as the 3rd argument of the `create()` method. The callback should have the following signature:

```php
// $obj is the newly created object
function callback($obj);
```

Example:

```php
$factory = new GenericFactory();
$factory->setBaseClass('\Foo\BarInterface');
$factory->setArguments([
	'logger'=>$container['logger']
]);
// Create a new object with a callback.
$factory->create('\Foo\Bar', null, function(\Foo\Bar $obj) {
  // Outputs the newly created `\Foo\Bar` object
	var_dump($obj);
});
```

Another way of providing a callback is with the `setCallback()` method:

```php
$factory = new GenericFactory();
$factory->setCallback(function(\Foo\Bar $obj) {
	// Outputs the newly created '\Foo\Bar' object
	var_dump($obj);
});
```

# The `AbstractFactory` API

| Method                                 | Return value | Description |
| -------------------------------------- | ------------ | ----------- |
| `create(string $type [, array $args, callable $cb])` | _Object_     | Create a class from a "type" string. |
| `get(string $type [, array $args])`    | _Object_     | Get returns the latest created class instance, or a new one if none exists. |
| `setBase_class(string $classname)`    | _Chainable_  |             |
| `baseClass()`                         | `string`     |             |
| `setDefaultClass(string $classname)`  | _Chainable_  |             |
| `defaultClass()`                      | `string`     |             |
| `resolve(string $type)`               | `string`     | **abstract**, must be reimplemented in children classes. |
| `isResolvable(string $type)`          | `boolean`    | **abstract**, must be reimplemented in children classes. |

## The `MapFactory` additional API

| Method                                       | Return value | Description |
| -------------------------------------------- | ------------ | ----------- |
| `addClass(string $type, string $class_name)` | _Chainable_  |             |
| `setMap(array $map)`                         | _Chainable_  |             |
| `map()`                                      | `array`      |             |

## The `GenericFactory` additional API

Because the `ClassNameFactory` uses the parameter directly, there is no additional methods for this type of class.

The `resolve()` method simply returns its _type_ argument, and the `validate()` method simply ensures its _type_ argument is a valid (existing) class.

## The `ResolverFactory` additional API

The `ResolverFactory` resolves the classname from the class resolver options:

- `resolverPrefix` `string` that will be prepended to the resolved class name.
- `resolverSuffix` `string` that will be appended to the resolved class name.
- `resolverCapitals` `array` of characters that will cause the next character to be capitalized.
- `resolverReplacements` `array`

| Method                                           | Return value | Description |
| -----------------------------------------------| ------------ | ----------- |
| `setResolverPrefix(string $prefix)`            | _Chainable_  |             |
| `resolverPrefix()`                             | `string`     |             |
| `setResolverSuffix(string $suffix)`            | _Chainable_  |             |
| `resolverSuffix()`                             | `string`     |             |
| `setResolveCcapitals(array $capitals)`         | _Chainable_  |             |
| `resolverCapitals()`                           | `array`      |             |
| `setResolverReplacements(array $replacements)` | _Chainable_  |             |
| `resolverReplacements()`                       | `array`      |             |

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

All Charcoal modules follow the same coding style and `charcoal-factory` is no exception. For PHP:

- [_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
- [_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
- [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), autoloading is therefore provided by _Composer_
- [_phpDocumentor_](http://phpdoc.org/)
	- Add DocBlocks for all classes, methods, and functions;
- Naming conventions
	- Prefix abstract classes with `Abstract`;
	- Suffix interfaces with `Interface`, traits with `Trait`, exceptions with `Exception`;
	- For arrays, use short notation `[]` (instead of `array()`).
	- Read the [phpcs.xml](phpcs.xml) file for all the details.

Coding styles are  enforced with `grunt phpcs` ([_PHP Code Sniffer_](https://github.com/squizlabs/PHP_CodeSniffer)). The actual ruleset can be found in `phpcs.xml`.

> 👉 To fix minor coding style problems, run `grunt phpcbf` ([_PHP Code Beautifier and Fixer_](https://github.com/squizlabs/PHP_CodeSniffer)). This tool uses the same ruleset as *phpcs* to automatically correct coding standard violations.

## Authors

- Mathieu Ducharme <mat@locomotive.ca>

## Changelog

### 0.3

- Add the `setArguments()` method to factories.
- Add the `setCallback()` method to factories.
- Execute the callback when using the default class too.


### 0.2

_Released 2016-01-26_

Minor (but BC-breaking) changes to Charcoal-Factory

- Full PSR1 compliancy (All methods are now camel-case).
- Add a callback argument to the `create()` method.
- `create()` and `get()` are now `final` in the base abstract factory class.
- Internal code, docs and tool improvements.


### 0.1

_Released 2015-11-25_

- Initial release
