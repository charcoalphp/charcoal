Charcoal Image
==============

[![Build Status](https://travis-ci.org/locomotivemtl/charcoal-image.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-image)

Charcoal Image is a PHP image manipulation and processing library, providing a consistent API across different Image drivers. Currently supported drivers are `Imagick` (the PHP extension) and `Imagemagick` (using shell commands)

-   [API Documentation](docs/api.md)

# How to install

The preferred (and only supported) way of installing charcoal-image is with **composer**:

```shell
$ composer require locomotivemtl/charcoal-image
```

## Dependencies

-   `PHP 5.6+`
-   `locomotivemtl/charcoal-factory`
-   `ext-imagick` (optional but _recommended_)
**OR**
-   `ImageMagick binaries`

> 👉 Although this module was developped for `Charcoal`, there is absolutely no dependencies on any Charcoal modules and can therefore be used in any PHP project.

# Why another PHP image libary?

_Why not?_. Charcoal-Image has been developped and used in in-house projects for almost 10 years. It has recently been rewritten to a more modern PHP style and released under an open-source license (MIT).

The main differences between existing PHP libraries like _Imagine_ or _Intervention_ are:

-   Effect parameters are sent as an array.
    -   Is it `blur($sigma, $radius)` or `blur($radius, $sigma)`?
    -   With charcoal image it's constant: `blur([ 'radius' => $radius, 'sigma' => $sigma ]);`
-   It supports *ImageMagick* binaries
    -   It seems to be a pretty common setup where Imagemagick is installed on a server, but the _Imagick_ PHP library is not. charcoal-image can be used
-   No external dependencies, except the tiny `charcoal-factory`.

# Usage

Typically, _charcoal-image_ is used to load an image, perform operations (called _effects_ such as blur, resize, watermark, etc.) and write the modified image.

## With `setData()`

All effects can be added at once in a single array.

```php
$img = new \Charcoal\Image\Imagick\ImagickImage();
$img->setData([
    'source'  => 'example.png',
    'target'  => 'example-modified.png',
    'effects' => [
        [
            'type'  => 'resize',
            'width' => 600
        ],
        [
            'type'  => 'blur',
            'mode'  => 'gaussian',
            'sigma' => 5
        ]
    ]
]);
$img->process();
$img->save();
```

> `setData()` is perfect for scenario where the effects are from a JSON configuration structure, for example.

## With magic methods

All effects can also be used as methods on the image (using `__call()` magic).

```php
use Charcoal\Image\Imagick\ImagickImage as Image;

$img = new Image();
$img->open('example.png');
$img->resize([
    'width' => 600
]);
$img->blur([
    'mode'  => 'gaussian',
    'sigma' => 5
]);
$img->save();
```

### Chainable version

Also shown: using the `ImageFactory` constructor method:

```php
use \Charcoal\Image\ImageFactory;

$factory = new ImageFactory();
$img = $factory->create('imagemagick');
$img->open('example.png')
    ->resize([
        'mode'   => 'best_fit',
        'width'  => 350
        'height' => 350
    ])
    ->rotate([
        'angle' => 90
    ])
    ->modulate([
        'luminance' => 50
    ])
    ->save('modified-target.png');
```

Available effects and operations are documented in the [API Documentation](docs/api.md).

# Available image drivers

There are currently only 2 available drivers:

-   `imagick`
    -   The imagick driver use the `Imagick` PHP extension, which is build on top of imagemagick.
-   `imagemagick`
    -   The imagemagick driver uses the imagmagick binaries directly, running the operations in a separate shell process instead of directely within PHP.
    -   The commands `convert`, `mogrify` and `identify` should be installed on the system and reachable from the PHP process.

> 👉 Comming soon, the `gd` driver to use PHP builtin's image capacity.

## How to select a driver

There are two different ways to instantiate an _Image_ object for a specific driver.

Directly:

```php
$img = new \Charcoal\Image\Imagick\ImagickImage();
// or
$img = new \Charcoal\Image\Imagemagick\ImagemagickImage();
```

With the provided `ImageFactory`:

```php
use \Charcoal\Image\ImageFactory;
$factory = new ImageFactory();

$img = $factory->create('imagick');
// or
$img = $factory->create('imagemagick');
```

# Development

To install the development environment:

```shell
★ composer install --prefer-source
```

To run the tests:

```shell
★ composer test
```

## Coding Style

All Charcoal modules follow the same coding style and `charcoal-image` is no exception. For PHP:

-   [_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
-   [_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
-   [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), autoloading is therefore provided by _Composer_
-   [_phpDocumentor_](http://phpdoc.org/)
-   Arrays should be written in short notation (`[]` instead of `array()`)

Coding styles are enforced with `grunt phpcs` ([_PHP Code Sniffer_](https://github.com/squizlabs/PHP_CodeSniffer)). The actual ruleset can be found in `phpcs.xml`.

> 👉 To fix minor coding style problems, run `grunt phpcbf` ([_PHP Code Beautifier and Fixer_](https://github.com/squizlabs/PHP_CodeSniffer)). This tool uses the same ruleset as *phpcs* to automatically correct coding standard violations.

The main PHP structure follow the [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md) standard. Autoloading is therefore provided by _Composer_.

To ensure a clean code base, pre-commit git hooks should be installed on all development environments.

## Continuous Integration

-   [Travis](https://travis-ci.org/locomotivemtl/charcoal-image)
-   [Sensio Labs](https://insight.sensiolabs.com/projects/87c9621d-3b2e-4e71-a42f-e69ebca4672e)
-   [Scrutinizer](https://scrutinizer-ci.com/)

## Unit Tests

Every class, method, and function should be covered by unit tests. PHP code can be tested with [_PHPUnit_](https://phpunit.de/).

## Authors

-   Mathieu Ducharme <mat@locomotive.ca>

## Changelog

### 0.3

_Released 2016-03-11_

-   Break BC in every way.
-   Convert to camelCase / Full PSR-1 / PSR-2 support.

### 0.2

_Released 2015-09-15_

-   Add a new "auto-orientation" effect (imagick + imagemagick)
-   Add the watermark effect to imagemagick (imagemagick)
-   Fixed the "unsharp mask" mode for sharpen effect (imagick)
-   Fixed the gravity for the watermark effect (imagick)
-   Accept _ImageInterface_ objects as watermark (global)
-   Add a dependency on `locomotivemtl/charcoal-factory` and fix factories accordingly. (global)

### 0.1

_Released 2015-08-26_

-   Initial release

## TODOs

-   Write a version for PHP's `gd` driver.
-   Custom Exceptions.
-   Change effect signature to be callable (invokable) instead of using the process() method.
-   Skip unit tests instead of failing if a driver is not available.
