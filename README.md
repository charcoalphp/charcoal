Charcoal Image
==============

Charcoal Image is a PHP image manipulation and processing library, currently built on top of `imagick`. 

[![Build Status](https://travis-ci.org/locomotivemtl/charcoal-image.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-image)

# How to install

The preferred way of installing this module is with `composer`. Using composer, an autoloader is provided automatically with `PSR-4` so there is no further requirement.

```shell
$ composer require locomotivemtl/charcoal-image
```

## Dependencies

- `PHP 5.4+`
- `ext-imagick`
  -  The `imagick` driver is the only available one at this time, and therefore is required as a hard dependency for now.

> 👉 Although this module was developped for `Charcoal`, there is absolutely no dependencies on any Charcoal modules and can therefore be used in any PHP project.

# Usage

## With `set_data()`

```php
$img = new \Charcoal\Image\Imagick\ImagickImage();
$img->set_data([
    'source'=>'example.png',
    'target'=>'example-modified.png',
    'effects'=>[
        [
            'type'=>'resize',
            'width'=>600
        ],
        [
            'type'=>'blur',
            'mode'=>'gaussian',
            'sigma'=>15
        ]
    ]
]);
$img->process();
$img->save();
```

## With magic (`__call()`) methods

```php
use \Charcoal\Image\Imagick\ImagickImage as Image;

$img = new Image();
$img->open('example.png');
$img->resize([
    'width'=>600
]);
$img->blur([
    'mode'=>'gaussian',
    'sigma'=>15
]);
$img->save();
```

### Chainable version

Also shown: using the `ImageFactory` constructor method:
```php
use \Charcoal\Image\ImageFactory;

$img = ImageFactory::instance()->get('imagemagick');
$img->open('example.png')
  ->resize([
    'mode'=>'best_fit',
    'width'=>350
    'height'=>350
  ])
  ->rotate([
    'angle'=>90
  ])
  ->modulate([
    'luminance'=>50
  ])
  ->save('modified-target.png');
```

# Available image drivers

There are currently only 2 available drivers:
- `imagick`
  - The imagick driver use the `Imagick` PHP extension, which is build on top of imagemagick.
- `imagemagick`
  - The imagemagick driver uses the imagmagick binaries directly, running the operations in a separate shell process instead of directely within PHP.
  - The commands `convert`, `mogrify` and `identify` should be installed on the system and reachable from the PHP process.

## How to select a driver

There are two different ways to instantiate an _Image_ object for a specific driver.

Directly:
```php
$img = new \Charcoal\Image\Imagick\ImagickImage();
// or
$img = new \Charcoal\Image\Imagemagick\ImagemagickImage();
```

With `ImageFactory`:
```php
use \Charcoal\Image\ImageFactory;
$img = ImageFactory::instance()->get('imagick');
// or
$img = ImageFactory::instance()->get('imagemagick');
```

# Available effects / operations

Any image operation is called an "effect" and implements the `\Charcoal\Image\EffectInterface` interface.

The available effects are:
- [Blur](#blur-effect)
- [Dither](#dither-effect)
- [Grayscale](#grayscale-effect)
- [Mask](#mask-effect)
- [Mirror](#mirror-effect)
- [Modulate](#modulate-effect)
- [Resize](#resize-effect)
- [Revert](#revert-effect)
- [Rotate](#rotate-effect)
- [Sepia](#sepia-effect)
- [Sharpen](#sharpen-effect)
- [Threshold](#threshold-effect)
- [Tint](#tint-effect)
- [Watermark](#watermark-effect)

## Blur Effect
**Blurs an image**

### Options
- `mode` (_string_)
  - The type of blur to apply. Possible valures are: `standard`, `adaptive`, gaussian`, `motion`, `radial`, `soft` (Currently unsupported)
- `radius` (_float_)
    - Defines the extent or size of the filter (technically, the size of the array which hold the calculated Gaussian distribution).
    - If 0, a default optimal radius will be auto-determined.
    - Bigger radius results in slower effect.
    - Default is `0`.
- `sigma` (_float_)
    - Determines the actual amount of blurring that will take place.
    - Default is `1`.

- `channel` (_int_)
- `angle` (_float_)
  - The angle of the blur. Only used in `motion` or `radial` mode.
  - Default is `0`.

### Blur Modes
- `standard`
  - Perform a standard blur operation on the image.
- `adaptive`
  - Perform an adaptive blur on the image. 
  - The intensity of an adaptive blur depends is dramatically decreased at edge of the image, whereas a standard blur is uniform across the image. 
- `gaussian`
  - Perform a gaussian blur on the image.
- `motion`
  - Simulate a motion blur effect on the image.
  - The "direction" of the blur is determined by the `angle` paramter. 
- `radial`
  - Perform a radial (circular) blur on the image. 
  - The `angle` argument is the angle the radial-blur coversThat is half that angle in each direction from the original image. So an angle of 180 is over a half circle, while 360 degrees will blur the image in a full circle.
  - The `sigma` and `radius` parameters are ignored in this mode.
- `soft`
  - Blend the blur with the original image.

> 👉 The `soft` mode is currently only available with the `imagemagick` driver.

## Dither Effect
**Reduces an image's colors to a certain number, using dithering.**

### Options
- `colors` (_int_)
  - The number of colors to reduce to

> 👉 The `dither` effect is currently only available with the `imagick` driver.

## Grayscale Effect
**Converts an image's colors to a 256-colors greyscale. There are no options.**

### Options
- _none_

## Mask Effect
**Apply a 8-bit transparency mask to the image.**

### Options
- `mask` (_string_)
- `opacity` (_float_)
- `gravity` (_string_)
- `x` (_integer_)
- `y` (_integer_)

> 👉 The `mask` effect is currently not supported by any driver.

## Mirror Effect
**Flip an image horizontally or vertically.**

### Options
 - `axis` (_string_)
   - The axis can be "x" (flip) or "y" (flop). 
   - Default is "y".

## Modulate Effect
**Modifies an image's colors in the special HSL (hue-saturation-luminance) colorspace.**

### Options
- `hue` (_float_)
  - The **color tint** value, between -100 and 100.
  - Default is `0` (no effect)
- `saturation` (_float_)
  - The **color intensity** value, between -100 and 100.
  - Default is `0` (no effect)
- `luminance` (_float_)
  - The **brightness** value, between -100 and 100.
  - Default is `0` (no effect)

## Resize Effect
**Resize an image to given dimensions.**

### Options
- `mode` (_string_)
  - The type of resize operation to perform 
  - Valid modes are: `auto`, `exact`, `width`, `height`, `best_fit`, `crop`, `fill` or `none`.
  - Default is `auto`.
- `width` (_integer_)
  - The target's width, in pixels.
  - Mandatory in `width`, `exact`, `best_fit`, `crop` and `fill` modes
  - Default is `0`.
- `height` (_integer_)
  - The target's height, in pixels.
  - Mandatory in `height`, `exact`, `best_fit`, `crop` and `fill` modes
  - Default is `0`.
- `gravity`
  - The gravity, only used in `crop` mode.
  - Defaults to `center`
- `background_color` (_string_)
  - The background color, only used in `fill` mode.
  - Can be specified as hexadecimal ("#FF00FF"), RGB values ("rgb(255,0,255)") or color name ("red").
  - Default is _transparent-white_ (`rgb(100%, 100%, 100%, 0)`)
- `adaptive`
  
### Resize Modes
- `auto`
  - Auto-determined from effect options.
- `exact`
  - Resize to an exact width and height (ignoring original ratio). 
- `width`
  - Resize to an exact width, keeping aspect ratio.
  - In this mode, the `width` parameter is required and the `height` parameter is ignored.
- `height`
  - Resize to an exact height, keeping aspect ratio.
  - In this mode, the `width` parameter is required and the `height` parameter is ignored.
- `best_fit`
  - Resize to the maximum `width` or `height`, keeping aspect ratio. 
- `crop`
  - Resize to the best match possible (oversized) to keep ratio and crop the superfluous data.
- `fill`
  - Resize to the best match possible (undersize) to keep ratio and fill the superfluous area with the `background_color`.
- `none`
  - Ignore resize operation entirely (do nothing) 

## Revert Effect
**Revert (negate) the image's colors.**

### Options
- `channel` (_string_)

## Rotate Effect
**Rotate the image by a certain angle.**

### Options
- `angle` (_float_)
    - The angle of rotation, in degrees (clockwise).
    - Note that the dimension of the image can be modified if it is not square or if the angle is not a multiple of 90 degrees.
    - Distortion will occur for any angle other than multiple of 90 degrees.
    - Default to 0. (No rotation)
- `background_color` (_string_)
    - The background color of the canvas.
    - Only used if the angle is not a multiple of _90_.
    - Can be specified as hexadecimal ("#FF00FF"), RGB values ("rgb(255,0,255)") or color name ("red").
    - Default is _transparent-white_ (`rgb(100%, 100%, 100%, 0)`)

## Sepia Effect
**Tint the image with a vintage, sepia look**

### Options
- `threshold` (_float_)
  - The level of the sepia tone effect.
  - Default is `75`.

## Sharpen Effect
**Sharpen an image, with a simple sharpen algorithm or unsharp mask options.**

### Options
- `mode` (_string_)
  - Can be `standard`, `adaptive` or `unsharp` 
- `radius` (_float_)
  - The sharpen radius 
  - Default is `0`.
- `sigma` (_float_)
  - Default is `1`. 
- `amount` (_float_)
  - The amount (or _gain_) to unsharp.
  - Only used in `unsharp` mode.
  - Default is `1` 
- `threshold` (_float_)
  - Only used in `unsharp` mode. 
  - Default is `0.05` 
- `channel`

### Sharpen Modes
- `standard`
  - Perform a simple, standard sharpen operation on the image.
- `adaptive`
- `unsharp`
  - Sharpen the image with a more complex   

## Threshold
**Convert the image to monochrome (black and white).**

### Options
- `threshold` (_float_)

## Tint Effect
**Tint (or colorize) an image with a certain color.**

### Options
- `color` (_string_)
    - Color to blend unto the image.
    - Can be specified as hexadecimal ("#FF00FF"), RGB values ("rgb(255,0,255)") or color name ("red").
    - Default is black (`rgb(0,0,0)`).
- `opacity` (_float_)
    - Percentage of the value to blend, as a float between 0.0 and 1.0
    - Default to 50% (`0.5`).
- `midtone` (_boolean_)
    - If true, then the color will be blended only to the midtones.
    - Technically, true call the _tint_ function while false calls the _colorize_ function.
    - Default is `true`.

## Watermark Effect
**Composite a watermark on top of the image.**

### Options
- `watermark` (_string_)
- `opacity` (_float_)
- `gravity` (_string_)
- `x` (_integer_)
- `y` (_integer_)

> 👉 The `watermark` effect is currently only available with the `imagick` driver.

## Future Effects
These effects are available in the `imagick` library and therefore could easily be added:
- `Charcoal`
- `Chop`
- `Fx`
- `Emboss`
- `Blueshift`
- `Border`
- `Edge`
- `Equalize`
- `Gamma`
- `Oilpaint`
- `Posterize`
- `Reducenoise`
- `Swirl`
- `Wave`

## Development

## Coding Style

All Charcoal modules follow the same coding style and `charcoal-core` is no exception. For PHP:

- [_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md), except for
  - Method names MUST be declared in `snake_case`.
- [_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md), except for
  - Private variables SHOULD be prefixed with a single underscore to indicate protected or private visibility. 
- [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), autoloading is therefore provided by _Composer_
- [_phpDocumentor_](http://phpdoc.org/)
- Arrays should be written in short notation (`[]` instead of `array()`)

Coding styles are  enforced with `grunt phpcs` ([_PHP Code Sniffer_](https://github.com/squizlabs/PHP_CodeSniffer)). The actual ruleset can be found in `phpcs.xml`.

> 👉 To fix minor coding style problems, run `grunt phpcbf` ([_PHP Code Beautifier and Fixer_](https://github.com/squizlabs/PHP_CodeSniffer)). This tool uses the same ruleset as *phpcs* to automatically correct coding standard violations.

The main PHP structure follow the [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md) standard. Autoloading is therefore provided by _Composer_.

## Automated Checks

Most quality checker tools can be run with _Grunt_. They are:

- `grunt phpunit`, to run the Test Suite.
- `grunt phpcs`, to ensure coding style compliance.

All three tools can also be run from `grunt watch`.

To ensure a clean code base, pre-commit git hooks should be installed on all development environments.

## Continuous Integration

- [Travis](https://travis-ci.org/locomotivemtl/charcoal-image)
- [Scrutinizer](https://scrutinizer-ci.com/)
- [Code Climate](https://codeclimate.com/)

## Unit Tests

Every class, method, and function should be covered by unit tests. PHP code can be tested with [_PHPUnit_](https://phpunit.de/).

## Authors

- Mathieu Ducharme <mat@locomotive.ca>

## Changelog

### 0.1
_Unreleased_

## TODOs

- Write a version for GD
- Custom Exceptions
- Change effect signature to be callable instead of using the process() method
