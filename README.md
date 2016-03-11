Charcoal Image
==============

Charcoal Image is a PHP image manipulation and processing library, providing a consistent API across different Image drivers. Currently supported drivers are `Imagick` (the PHP extension) and `Imagemagick` (using shell commands)

[![Build Status](https://travis-ci.org/locomotivemtl/charcoal-image.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-image)

# How to install

The preferred (and only supported) way of installing charcoal-image is with **composer**:

```shell
$ composer require locomotivemtl/charcoal-image
```

## Dependencies

- `PHP 5.5+`
- `locomotivemtl/charcoal-factory`
- `ext-imagick` (optional but _recommended_)
**OR**
- `ImageMagick binaries`

> 👉 Although this module was developped for `Charcoal`, there is absolutely no dependencies on any Charcoal modules and can therefore be used in any PHP project.

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

## With magic  methods

All effects can also be used as methods on the image (using `__call()` magic).

```php
use \Charcoal\Image\Imagick\ImagickImage as Image;

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

# Why another PHP image libary?

_Why not?_. Charcoal-Image has been developped and used in in-house projects for almost 10 years. It has recently been rewritten to a more modern PHP style and released under an open-source license (MIT).

The main differences between existing PHP libraries like _Imagine_ or _Intervention_ are:

- Effect parameters are sent as an array.
	- Is it `blur($sigma, $radius)` or `blur($radius, $sigma)`?
	- With charcoal image it's constant: `blur(['radius' => $radius, 'sigma' => $sigma]);`
- It supports *ImageMagick* binaries
	- It seems to be a pretty common setup where Imagemagick is installed on a server, but the _Imagick_ PHP library is not. charcoal-image can be used
- No external dependencies, except the tiny `charcoal-factory`.

# Available image drivers

There are currently only 2 available drivers:

- `imagick`
	- The imagick driver use the `Imagick` PHP extension, which is build on top of imagemagick.
- `imagemagick`
	- The imagemagick driver uses the imagmagick binaries directly, running the operations in a separate shell process instead of directely within PHP.
	- The commands `convert`, `mogrify` and `identify` should be installed on the system and reachable from the PHP process.

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

# Available effects / operations

Any image operation is called an "effect" and implements the `\Charcoal\Image\EffectInterface` interface.

The available effects are:

- [Auto-orientation](#auto-orientation-effect)
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

## Auto-orientation Effect

**Reads image EXIF data to automatically rotate it to the proper orientation**

### Options

- _none_

### Examples

![Default image](docs/images/portrait-5.jpg)

- Default image (with EXIF orientation #5)

![Auto-orientation](docs/images/portrait-5-autoorientation.jpg)

- Auto-orientation (with EXIF orientation #1): `$img->autoorientation();`

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
	- Note that the `channel` value is ignored in this mode.

> 👉 The `soft` mode is currently only available with the `imagemagick` driver.

### Examples

![Gaussian blur](docs/images/flower/imagick-blur-gaussian-strong.png)

- Gaussian blur: `$img->blur(['mode' => 'gaussian', 'radius' => 5, 'sigma' => 5]);`

![Radial blur](docs/images/panda/imagick-blur-radial-8.png)

- Radial blur: `$img->blur('mode' => 'radial', 'angle' => 8]);`

## Dither Effect

**Reduces an image's colors to a certain number, using dithering.**

### Options

- `colors` (_int_)
	- The number of colors to reduce to

> 👉 The `dither` effect is currently only available with the `imagick` driver.

### Examples

![Dither 3](docs/images/panda/imagick-dithers-3colors.png)

- Dither wit 3 colors: `$img->dither(['colors' => 3]);`

## Grayscale Effect

**Converts an image's colors to a 256-colors greyscale. There are no options.**

### Options

- _none_

### Examples

![Grayscale](docs/images/panda/imagick-grayscale-default.png)

- Grayscale: `$img->grayscale();`

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

### Examples
![X-axis mirror](docs/images/panda/imagick-mirror-x.png)

- Mirror (x-axis): `$img->mirror(['axis' => 'x']);`

![Y-axis mirror](docs/images/flower/imagick-mirror-y.png)

- Mirror (x-axis): `$img->mirror(['axis' => 'y']);`

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

### Examples

![Modulate brightness](docs/images/flower/imagick-modulate-brightness.png)

- Modulate with brightness only: `$img->modulate(['luminance' => 50]);`

![HSL](docs/images/panda/imagick-modulate-hsl.png)

- Modulate HSL: `$img->modulate(['luminance' => 20, 'hue' => -20, 'saturation' => 40]);`

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

### Examples

![Revert](docs/images/panda/imagick-revert-default.png)

- Revert default (all channels): `$img->revert();`

![Revert](docs/images/panda/imagick-revert-red.png)

- Revert red channel: `$img->revert(['channel' => 'red']);`

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

### Examples

![Rotate 90](docs/images/flower/imagick-rotate-90.png)

- Rotate 90: `$img->rotate(['angle' => 90]);`

![Rotate -135](docs/images/flower/imagick-rotate-135.png)

- Rotate -135: `$img->rotate(['angle' => -135]);``

![Rotate 135 black](docs/images/flower/imagick-rotate-135-black.png)

- Rotate 135, black background: `$img->rotate(['angle' => 135, 'background_color' => 'black']);`

## Sepia Effect

**Tint the image with a vintage, sepia look**

### Options

- `threshold` (_float_)
	- The level of the sepia tone effect.
	- Default is `75`.

### Examples

![Sepia Default (75)](docs/images/flower/imagick-sepia-default.png)

- Default sepia (75): `$img->sepia();`

![Sepia 115](docs/images/flower/imagick-sepia-115.png)

- Default sepia (75): `$img->sepia(['threshold' => 115]);`

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

### Examples

![Threshold](docs/images/flower/imagick-threshold-default.png)

- Default threshold: `$img->threshold();`

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

### Examples

![Tint / colorize (red)](docs/images/panda/imagemagick-tint-red-colorize.png)

- Colorize / Tint red: `$img->tint(['color' => 'rgb(100%,0,0)', 'midtone' => false]);`

![Tint midtones (red)](docs/images/panda/imagemagick-tint-red.png)

- Midtone tint red: `$img->tint(['color' => 'rgb(100%,0,0)']);`

## Watermark Effect

**Composite a watermark on top of the image.**

### Options

- `watermark` (_string_ or _ImageInterface_)
- `opacity` (_float_)
- `gravity` (_string_)
- `x` (_integer_)
- `y` (_integer_)

### Notes

- The effect of the `x` and `y` values depends of the `gravity`.
	- Default gravity is "nw" for a top-left watermark. Set to "center" to align to center.
- The `opacity` option is currently ignored by both drivers.
- The watermark image will always be rescaled to fit inside the image's container.
- It will also always be moved to ensure the watermark is inside the image's container.

### Examples

![Watermark](docs/images/panda/imagick-watermark-default.png)

- Watermark: `$img->watermark([ 'watermark => 'charcoal.png' ]);`

## Advanced (chained effects) example

![Advanced example](docs/images/advanced-example.png)
```php
use \Charcoal\Image\Imagick\ImagickImage as Image;;

$watermark = new Image();
$watermark->open(__DIR__.'/tests/examples/watermark.png')
	->blur([ 'mode' => 'gaussian', 'sigma' => 2 ])
	->rotate([ 'angle' => 45 ])
	->tint([ 'color' => 'red', 'midtone' => false ]);

$watermark2 = new Image();
$watermark2->open(__DIR__.'/tests/examples/watermark.png')
	->blur([ 'mode' => 'gaussian', 'sigma' => 1 ])
	->tint([ 'color' => 'blue', 'midtone' => false ]);

$img = new Image();
$img->open(__DIR__.'/tests/examples/test02.png')
	->mirror([ 'axis' => 'y' ])
	->sharpen([ 'mode' => 'unsharp', 'amount' => 2 ])
	->watermark([ 'watermark' => $watermark, 'gravity' => 'ne' ])
	->watermark([ 'watermark' => $watermark2, 'gravity' => 'w' ])
	->modulate([ 'luminance' => 25 ])
	->save(__DIR__.'/out.png');
```

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

# Development

## Coding Style

All Charcoal modules follow the same coding style and `charcoal-image` is no exception. For PHP:

- [_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
- [_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
- [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), autoloading is therefore provided by _Composer_
- [_phpDocumentor_](http://phpdoc.org/)
- Arrays should be written in short notation (`[]` instead of `array()`)

Coding styles are enforced with `grunt phpcs` ([_PHP Code Sniffer_](https://github.com/squizlabs/PHP_CodeSniffer)). The actual ruleset can be found in `phpcs.xml`.

> 👉 To fix minor coding style problems, run `grunt phpcbf` ([_PHP Code Beautifier and Fixer_](https://github.com/squizlabs/PHP_CodeSniffer)). This tool uses the same ruleset as *phpcs* to automatically correct coding standard violations.

The main PHP structure follow the [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md) standard. Autoloading is therefore provided by _Composer_.

## Automated Checks

Most quality checker tools can be run with _Grunt_. They are:

- `grunt phpunit`, to run the Test Suite.
- `grunt phpcs`, to ensure coding style compliance.

To ensure a clean code base, pre-commit git hooks should be installed on all development environments.

## Continuous Integration

- [Travis](https://travis-ci.org/locomotivemtl/charcoal-image)
- [Sensio Labs](https://insight.sensiolabs.com/projects/87c9621d-3b2e-4e71-a42f-e69ebca4672e)
- [Scrutinizer](https://scrutinizer-ci.com/)

## Unit Tests

Every class, method, and function should be covered by unit tests. PHP code can be tested with [_PHPUnit_](https://phpunit.de/).

## Authors

- Mathieu Ducharme <mat@locomotive.ca>

## Changelog

### 0.3

_Released 2016-03-11_

- Break BC in every way.
- Convert to camelCase / Full PSR-1 / PSR-2 support.

### 0.2

_Released 2015-09-15_

- Add a new "auto-orientation" effect (imagick + imagemagick)
- Add the watermark effect to imagemagick (imagemagick)
- Fixed the "unsharp mask" mode for sharpen effect (imagick)
- Fixed the gravity for the watermark effect (imagick)
- Accept _ImageInterface_ objects as watermark (global)
- Add a dependency on `locomotivemtl/charcoal-factory` and fix factories accordingly. (global)

### 0.1

_Released 2015-08-26_

- Initial release

## TODOs

- Write a version for PHP's `gd` driver.
- Custom Exceptions.
- Change effect signature to be callable (invokable) instead of using the process() method.
- Skip unit tests instead of failing if a driver is not available.
