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
  - For type-hinting, use `int` (instead of `integer`) and `bool` (instead of `boolean`);
  - For casting, use `int` (instead of `integer`) and `!!` (instead of `bool` or `boolean`);
  - For arrays, use `[]` (instead of `array()`).

Coding styles are  enforced with `grunt phpcs` ([_PHP Code Sniffer_](https://github.com/squizlabs/PHP_CodeSniffer)). The actual ruleset can be found in `phpcs.xml`.

> 👉 To fix minor coding style problems, run `grunt phpcbf` ([_PHP Code Beautifier and Fixer_](https://github.com/squizlabs/PHP_CodeSniffer)). This tool uses the same ruleset as *phpcs* to automatically correct coding standard violations.

The main PHP structure follow the [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md) standard. Autoloading is therefore provided by _Composer_.

## Authors

- Mathieu Ducharme <mat@locomotive.ca>

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
