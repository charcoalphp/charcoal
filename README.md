Charcoal Config
===============

Configuration container for all things Charcoal.

[![Build Status](https://travis-ci.org/locomotivemtl/charcoal-config.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-config)

This package provides easy hierarchical configuration container (for config storage and access). 
`Charcoal\Config` acts as a configuration registry / repository.

## Supported file formats
There are currently 3 supported file formats: `ini`, `json` and `php`.
To load configuration from a file:

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

## How to use
```php
$config = new \Charcoal\GenericConfig();
$config->set_separator('.'); // Default is "/"
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

## Configuration chaining

## Interoperability
The `\Charcoal\Config` container implements the `container-interop` interface.
See [https://github.com/container-interop/container-interop]

## Changelog

### 0.1.1
_Unreleased_
- Removed the second argument for the constructor (currently unused)
- Clearer error message on invalid JSON files
- Fix composer.json and the autoloader
- Various internal changes (PSR2 compliancy, _with psr1 exception_)

### 0.1
_Released on 2015-08-25_
- Initial release of `charcoal-config`, 
