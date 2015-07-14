Charcoal Image
==============

Charcoal Image is a PHP image manipulation and processing library, currently built on top of `imagick`. 


## How to install

The preferred way of installing this module is with `composer`. Using composer, an autoloader is provided automatically with `PSR-4` so there is no further requirement.

```shell
$ composer require locomotivemtl/charcoal-image
```

## Dependencies

- `PHP 5.4+`
- `ext-imagick`
  -  The `imagick` driver is the only available one at this time, and therefore is required as a hard dependency for now.

> 👉 Although this module was developped for `Charcoal`, there is absolutely no dependencies on any Charcoal modules and can therefore be used in any PHP project.

## Usage

### With `set_data()`

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

### With magic (`__call()`) methods

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

#### Chainable version

Also shown: using the `ImageFactory` constructor method:
```php
use \Charcoal\Image\ImageFactory;

$img = ImageFactory::instance()->get('imagick');
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

## Available effects / operations

Any image operation is called an "effect" and implements the `\Charcoal\Image\EffectInterface` interface.

The available effects are:
- Blur
- Dither
- Grayscale
- Mask
- Mirror
- Modulate
- Resize
- Revert
- Rotate
- Sepia
- Sharpen
- Threshold
- Tint
- Watermark


### Blur Effect
**Blurs an image**

#### Options
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
  - 

#### Modes
- `standard`
- `adaptive`
- `gaussian`
- `motion`
- `radial`
- `soft`

### Dither Effect
**Reduces an image's colors to a certain number, using dithering.**

#### Options
- `colors` (_int_)
  - The number of colors to reduce to

### Grayscale Effect
**Converts an image's colors to a 256-colors greyscale. There are no options.**

#### Options
- _none_

### Mask Effect
**Apply a 8-bit transparency mask to the image.**

#### Options
- `mask` (_string_)
- `opacity` (_float_)
- `gravity` (_string_)
- `x` (_integer_)
- `y` (_integer_)

### Mirror Effect
**Flip an image horizontally or vertically.**

#### Options
 - `axis` (_string_)
   - The axis can be "x" (flip) or "y" (flop). 
   - Default is "y".

### Modulate Effect
**Modifies an image's colors in the special HSL (hue-saturation-luminance) colorspace.**

#### Options
- `hue` (_float_)
  - The **color tint** value, between -100 and 100.
  - Default is `0` (no effect)
- `saturation` (_float_)
  - The **color intensity** value, between -100 and 100.
  - Default is `0` (no effect)
- `luminance` (_float_)
  - The **brightness** value, between -100 and 100.
  - Default is `0` (no effect)

### Resize Effect
**Resize an image to given dimensions.**

#### Options
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
  
#### Resize Modes
- `auto`
- `exact`
- `width`
- `height`
- `best_fit`
- `crop`
- `fill`
- `none`

### Revert
**Revert (negate) the image's colors.**

#### Options
- `channel` (_string_)

### Rotate
**Rotate the image by a certain angle.**

#### Options
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

### Sepia
**Tint the image with a vintage, sepia look**

#### Options
- `threshold` (_float_)
  - The level of the sepia tone effect.
  - Default is `75`.

### Sharpen
**Sharpen an image, with a simple sharpen algorithm or unsharp mask options.**

#### Options
- `mode`
- `radius`
- `sigma`
- `amount`
- `threshold`
- `channel`

### Threshold
**Convert the image to monochrome (black and white).**

#### Options
- `threshold` (_float_)

### Tint 
**Tint (or colorize) an image with a certain color.**

#### Options
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

### Watermark
**Composite a watermark on top of the image.**

#### Options
- `watermark` (_string_)
- `opacity` (_float_)
- `gravity` (_string_)
- `x` (_integer_)
- `y` (_integer_)

### Future Effects
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

### Coding Style

All Charcoal modules follow the same coding style and `charcoal-core` is no exception. For PHP:

- [_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md), except for
  - Method names MUST be declared in `snake_case`.
- [_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
- [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), autoloading is therefore provided by _Composer_
- [_phpDocumentor_](http://phpdoc.org/)
- Arrays should be written in short notation (`[]` instead of `array()`)

Coding styles are  enforced with `grunt phpcs` ([_PHP Code Sniffer_](https://github.com/squizlabs/PHP_CodeSniffer)). The actual ruleset can be found in `phpcs.xml`.

> 👉 To fix minor coding style problems, run `grunt phpcbf` ([_PHP Code Beautifier and Fixer_](https://github.com/squizlabs/PHP_CodeSniffer)). This tool uses the same ruleset as *phpcs* to automatically correct coding standard violations.

The main PHP structure follow the [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md) standard. Autoloading is therefore provided by _Composer_.

### Automated Checks

Most quality checker tools can be run with _Grunt_. They are:

- `grunt phpunit`, to run the Test Suite.
- `grunt phpcs`, to ensure coding style compliance.

All three tools can also be run from `grunt watch`.

To ensure a clean code base, pre-commit git hooks should be installed on all development environments.

### Continuous Integration

- [Travis](https://travis-ci.org/)
- [Scrutinizer](https://scrutinizer-ci.com/)
- [Code Climate](https://codeclimate.com/)

### Unit Tests

Every class, method, and function should be covered by unit tests. PHP code can be tested with [_PHPUnit_](https://phpunit.de/).

## Authors

- Mathieu Ducharme <mat@locomotive.ca>

## Changelog

- Unreleased.

## TODOs

- Write a version for GD
- Write a version using the binary imagemagick tools directly ('mogrify')
- Custom Exceptions
