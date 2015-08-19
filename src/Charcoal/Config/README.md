Charcoal Config
===============

This package provides easy hierarchical configuration storage and access. `Charcoal\Config` acts as a configuration registry / repository.

## How to use
```php
$config = new \Charcoal\GenericConfig();
$config->set('foo', [
	'baz'=>example,
	'bar'=>42
]);
// Ouput "42"
echo $config->get('foo/bar');
```

Usage with ArrayAccess:
```php
$config = new \Charcoal\GenericConfig();
$config['foo'] = [
    'baz'=>'example',
    'bar'=>42
];
// Output 42
echo $config['foo/bar'];
```


