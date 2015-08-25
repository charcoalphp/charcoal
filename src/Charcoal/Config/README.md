Charcoal Config
===============

This package provides easy hierarchical configuration container (storage and access). `Charcoal\Config` acts as a configuration registry / repository.

## How to use
```php
$config = new \Charcoal\GenericConfig();
$config->set('foo', [
	'baz'=>example,
	'bar'=>42
]);
// Ouput "42"
echo $config->get('foo.bar');
```

Usage with `ArrayAccess`, and setting data from the constructor
```php
$config = new \Charcoal\GenericConfig([
    'foo' => [
        'baz'=>'example',
        'bar'=>42
    ]
]);
// Output "example"
echo $config['foo/baz'];
```

Note that the previous example uses the default separator, which is `/`.
To use a different separator (for dot notation, for example) use:
```php
$config->set_separator('.');
```

## Interoperability
The `\Charcoal\Config` container implements the `container-interop` interface.
See [https://github.com/container-interop/container-interop]
