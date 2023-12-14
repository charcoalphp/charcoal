Charcoal Factory
================

The Factory package provides abstract object factories to create objects.

## Installation

```shell
composer require charcoal/factory
```

## Usage

Factories can resolve a _type_ to a FQN and create instance of this class with an optional given set of arguments, while ensuring a default base class.

Factory options should be set directly from the constructor:

```php
$factory = new Charcoal\Factory\GenericFactory([
    // Ensure the created object is a Charcoal Model
    'base_class' => '\Charcoal\Model\ModelInterface',

    // An associative array of class map (aliases)
    'map' => [
        'foo' => '\My\Foo',
        'bar' => '\My\Bar',
    ],

    // Constructor arguments
    'arguments' => [
        $dep1,
        $dep2,
    ],

    // Object callback
    'callback' => function ($obj) {
        $obj->do('foo');
    },
]);

// Create a "\My\Custom\Baz" object with the given arguments + callbck
$factory->create('\My\Custom\Baz');

// Create a "\My\Foo" object (using the map of aliases)
$factory->create('foo');

// Create a "\My\Custom\FooBar" object with the default resolver
$factory->create('my/custom/foo-bar');
```

Constructor options (class dependencies) are:

| Name               | Type       | Default              | Description |
| ------------------ | ---------- | -------------------- | ----------- |
| `base_class`       | _string_   | `''`                 | Optional. A base class (or interface) to ensure a type of object.
| `default_class`    | _string_   | `''`                 | Optional. A default class, as fallback when the requested object is not resolvable.
| `arguments`        | _array_    | `[]`                 | Optional. Constructor arguments that will be passed along to created instances.
| `callback`         | _callable_ | `null`               | Optional. A callback function that will be called upon object creation.
| `resolver`         | _callable_ | `null`<sup>[1]</sup> | Optional. A class resolver. If none is provided, a default will be used.
| `resolver_options` | _array_    | `null`               | Optional. Resolver options (prefix, suffix, capitals and replacements). This is ignored / unused if `resolver` is provided.

Notes:

* <sup>[1]</sup> If no resolver is provided, a default `\Charcoal\Factory\GenericResolver` will be used.

### Class resolver

The _type_ (class identifier) sent to the `create()` method can be parsed / resolved with a custom `Callable` resolver.

If no `resolver` is passed to the constructor, a default `\Charcoal\Factory\GenericResolver` is used. This default resolver transforms, for example, `my/custom/foo-bar` into `\My\Custom\FooBar`.

### Class map and aliases

Class _aliases_ can be added by setting them in the Factory constructor:

```php
$factory = new GenericFactory([
    'map' => [
        'foo' => '\My\Foo',
        'bar' => '\My\Bar',
    ],
]);

// Create a `\My\Foo` instance
$obj = $factory->create('foo');
```

### Ensuring a type of object

Ensuring a type of object can be done by setting the `base_class` property.

The recommended way of setting the base class is by setting it in the constructor:

```php
$factory = new GenericFactory([
    'base_class' => '\My\Foo\BaseClassInterface',
]);
```

> 👉 Note that _Interfaces_ can also be used as a factory's base class.

### Setting a default type of object

It is possible to set a default type of object (default class) by setting the `default_class` property.

The recommended way of setting the default class is by setting it in the constructor:

```php
$factory = new GenericFactory([
    'default_class' => '\My\Foo\DefaultClassInterface',
]);
```

> ⚠️ Setting a default class name changes the standard Factory behavior. When an invalid class name is used, instead of throwing an `Exception`, an object of the default class type will **always** be returned.

### Constructor arguments

It is possible to set "automatic" constructor arguments that will be passed to every created object.

The recommended way of setting constructor arguments is by passing an array of arguments to the constructor:

```php
$factory = new GenericFactory([
    'arguments' => [
        [
            'logger' => $container['logger'],
        ],
        $secondArgument,
    ],
]);
```

### Executing an object callback

It is possible to execute an object callback upon object instanciation. A callback is any `Callable` that takes the newly created object by reference as its function parameter.

```php
// $obj is the newly created object
function callback($obj): void;
```

The recommended way of adding an object callback is by passing a `Callable` to the constructor:

```php
$factory = new GenericFactory([
    'arguments' => [[
        'logger' => $container['logger']
    ]],
    'callback' => function ($obj) {
        $obj->foo('bar');
        $obj->logger->debug('Objet instanciated from factory.');
    }
]);
```

## Resources

* [Contributing](https://github.com/charcoalphp/.github/blob/main/CONTRIBUTING.md)
* [Report issues](https://github.com/charcoalphp/charcoal/issues) and
  [send pull requests](https://github.com/charcoalphp/charcoal/pulls)
  in the [main Charcoal repository](https://github.com/charcoalphp/charcoal)
