Charcoal Image
==============

The Image package provides a consistent API for image manipulation and processing
with integrations for ~GD~ (coming soon) and ImageMagick (via the PHP extension or via shell commands).

## Installation

```shell
composer require charcoal/image
```

## Overview

### Why another PHP image libary?

_Why not?_. Charcoal Image has been developped and used in in-house projects for almost 10 years. It has recently been rewritten to a more modern PHP style and released under an open-source license (MIT).

The main differences between existing PHP libraries like _Imagine_ or _Intervention_ are:

* Effect parameters are sent as an array.
  * Is it `blur($sigma, $radius)` or `blur($radius, $sigma)`?
  * With charcoal image it's constant: `blur([ 'radius' => $radius, 'sigma' => $sigma ]);`
* It supports *ImageMagick* binaries
  * It seems to be a pretty common setup where Imagemagick is installed on a server, but the _Imagick_ PHP library is not.
* No external dependencies, except the tiny [charcoal/factory].

## Usage

Typically, the Image package is used to load an image, perform operations (called _effects_ such as blur, resize, watermark, etc.) and write the modified image.

### With `setData()`

All effects can be added at once in a single array.

```php
$img = new Charcoal\Image\Imagick\ImagickImage();

$img->setData([
    'source'  => 'example.png',
    'target'  => 'example-modified.png',
    'effects' => [
        [
            'type'  => 'resize',
            'width' => 600,
        ],
        [
            'type'  => 'blur',
            'mode'  => 'gaussian',
            'sigma' => 5,
        ],
    ],
]);
$img->process();
$img->save();
```

> `setData()` is perfect for scenario where the effects are from a JSON configuration structure, for example.

### With magic methods

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

#### Chainable version

Also shown: using the `ImageFactory` constructor method:

```php
use Charcoal\Image\ImageFactory;

$factory = new ImageFactory();
$img = $factory->create('imagemagick');
$img->open('example.png')
    ->resize([
        'mode'   => 'best_fit',
        'width'  => 350,
        'height' => 350,
    ])
    ->rotate([
        'angle' => 90,
    ])
    ->modulate([
        'luminance' => 50,
    ])
    ->save('modified-target.png');
```

Available effects and operations are documented in the [API Documentation](docs/api.md).

### Available image drivers

There are currently only 2 available drivers:

* `imagick`
  * The imagick driver use the `Imagick` PHP extension, which is build on top of imagemagick.
* `imagemagick`
  * The imagemagick driver uses the imagmagick binaries directly, running the operations in a separate shell process instead of directely within PHP.
  * The commands `convert`, `mogrify` and `identify` should be installed on the system and reachable from the PHP process.

> 👉 Comming soon, the `gd` driver to use PHP builtin's image capacity.

### How to select a driver

There are two different ways to instantiate an _Image_ object for a specific driver.

Directly:

```php
$img = new Charcoal\Image\Imagick\ImagickImage();
// or
$img = new Charcoal\Image\Imagemagick\ImagemagickImage();
```

With the provided `ImageFactory`:

```php
use Charcoal\Image\ImageFactory;

$factory = new ImageFactory();

$img = $factory->create('imagick');
// or
$img = $factory->create('imagemagick');
```

## Resources

* [Contributing](https://github.com/charcoalphp/charcoal/blob/main/CONTRIBUTING.md)
* [Report issues](https://github.com/charcoalphp/charcoal/issues) and
  [send pull requests](https://github.com/charcoalphp/charcoal/pulls)
  in the [main Charcoal repository](https://github.com/charcoalphp/charcoal)

[charcoal/factory]: https://github.com/charcoalphp/factory
