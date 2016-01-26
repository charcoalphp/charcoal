Charcoal View
=============

The `Charcoal\View` module provides everything needed to render templates and add renderer to objects.

[![Build Status](https://travis-ci.org/locomotivemtl/charcoal-view.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-view)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/396d2f06-82ba-4c79-b8cc-762f1e8bda29/mini.png)](https://insight.sensiolabs.com/projects/396d2f06-82ba-4c79-b8cc-762f1e8bda29)


# How to install

The preferred (and only supported) way of installing charcoal-view is with **composer**:

```shell
$ composer require locomotivemtl/charcoal-view
```

## Dependencies

- `PHP 5.5+`
	- Older versions of PHP are deprecated, therefore not supported.
- `psr/log`
	- A PSR-3 compliant logger should be provided to the various services / classes.
- `psr/http-message`
  - Charcoal View provides a PSR7 renderer.
- [`locomotivemtl/charcoal-config`](https://github.com/locomotivemtl/charcoal-config)
	- The view objects are _configurable_ with `\Charcoal\View\ViewConfig`.

### Optional dependencies

- [`mustache/mustache`](https://github.com/bobthecow/mustache.php)
	- The default rendering engine is _mustache_, so it should be included in most cases.
	- All default charcoal modules use mustache templates.
- [`twig/twig`](http://twig.sensiolabs.org/)
	- Twig can also be used as a rendering engine for the view.

> 👉 Development dependencies are described in the _Development_ section of this README file.

# Usage

Typical usage, through _Viewable_:

```php
class MyClass implements \Charcoal\View\ViewableInterface
{
	use \Charcoal\View\ViewableTrait;
	// ...
}

$viewable = new MyClass();
$viewable->setView($container['view']);
echo $viewable->renderTemplate('foo/bar/template');
```

Full example of the API:
```php
$engine = new \Charcoal\View\Mustache\MustacheEngine([
	'logger' => $logger, // PSR-3 logger
	'loader' => // ...
]);
$view = new \Charcoal\View\GenericView();
$view->setEngine($engine)

$context = new \Foo\Bar\ContextData();

echo $view->render('foo/bar/template', $context);
```

> 👉 The default view engine, used in those examples, would be _mustache_.

Using renderer, with a PSR7 framework (in this example, Slim 3):

```php
use \Charcoal\View\GenericView;
use \Charcoal\View\Renderer;

include 'vendor/autoload.php';

$app = new \Slim\App();
$container = $app->getContainer();

$container['view_config'] = function($c) {
	$config = new \Charcoal\View\ViewConfig();
	return $config;
};
$container['view'] = function($c) {
	return new GenericView([
		'config' => $c['view_config'],
		'logger' => $c['logger']
	]);
};
$container['renderer'] = function($c) {
	return new Renderer($c['view']);
};

$app->get('/hello/{name}', function ($request, $response, $args) {
	return $this->renderer->render($response, 'hello', $args);
});

$app->run();
```

## Views

The `Charcoal\View\ViewInterface` defines all that is needed to render templates via a view engine:

- `render($templateIdent = null, $context = null)`
- `renderTemplate($templateString, $context = null)`

The abstract class `Charcoal\View\AbstractView` fully implements the `ViewInterface` and adds the methods:

- `setEngine($engine)`
- `engine()`

### Generic view

As convenience, the `\Charcoal\View\GenericView` class implements the full interface by extending the `AbstractView` base class.

```php
use \Charcoal\View\GenericView;

// Using with a loader / template ident
$view = new GenericView([
	'engine_type' => 'mustache'
]);
$context = new \Foo\Bar\ModelController();
echo $view->render('example/foo/bar', $context);

// Using with a template string, directly
$view = new GenericView([
	'engine_type' => 'mustache'
]);
$template = '<p>Hello {{world}}</p>';
$context  = [
	'world' => 'World!'
];
echo $view->render($template, $context);
```

## View Engines

Charcoal _views_ support different templating Engines_, which are responsible for loading the appropriate template (through a _loader_) and render a template with a given context according to its internal rules. Every view engines should implement `\Charcoal\View\EngineInterface`.

There are 3 engines available by default:

- `mustache` (**default**)
- `php`
- `php-mustache`, the mustache templating engine with a PHP template loader. (Files are parsed with PHP before being used as a mustache templates).

### Templates

Templates are simply files, stored on the filesystem, containing the main view (typically, HTML code + templating tags, but can be kind of text data).

- For the *mustache* engine, they are `.mustache` files.
- For the *php* and *php-mustache* engines, they are `.php` files.

Templates are loaded with template _loaders_. Loaders implement the `Charcoal\View\LoaderInterface` and simply tries to match an identifier (passed as argument to the `load()` method) to a file on the filesystem.

Calling `$view->renderTemplate($template_ident, $ctx)` will automatically use the `Loader` object to find the template `$template_ident`.

Otherwise, calling `$view->render($template_string, $ctx)` expects an already-loaded template string.

## Viewable Interface and Trait

Any objects can be made renderable (viewable) by implementing the `Charcoal\View\ViewableInterface` by using the `Charcoal\View\ViewableTrait`.

The interface adds the following methods:

- `setTemplateEngine($engine)`
- `templateEngine()`
- `setTemplateIdent($ident)`
- `templateIdent()`
- `setView($view)`
- `view()`
- `display($template = null)`
- `render($template = null)`
- `renderTemplate($template_ident)`

The viewable trait also adds the following abstract methods:

- `createView` (**abstract** / _private_)

### Examples

Given the following classes:

```php
use \Charcoal\View\ViewableInterface;
use \Charcoal\View\ViewableTrait;

class MyObject implements ViewableInterface
{
	use ViewableTrait;

	public function world()
	{
		return 'world!';
	}
}

```

The following code:

```php
$obj = new MyObject();
$obj->render('Hello {{world}}');
```

would output: `"Hello world!"`

## Classes summary

- `\Charcoal\View\AbstractEngine`
	- Base abstract class for _Engine_ interfaces, implements `EngineInterface`.
	- Is the base of all 3 engines: `mustache`, `php`, `php-mustache`.
- `\Charcoal\View\AbstractView`
	-  Base abstract class for _View_ interfaces, implements `ViewInterface`.
	-  Also implements `\Charcoal\Config\ConfigurableInterface` through the `ConfigurableTrait`
	-  Can be cast to string (will call `render()`)
	-  *Dependencies*: a `ViewConfig` configuration object and a PSR-3 logger
- `\Charcoal\View\EngineInterface`
	- _Engines_ are the actual template renderers for the views.
	- *Dependencies*: a Loader (MustacheLoader)
- `\Charcoal\View\GenericView`
	- Concrete implementation of a _View_ interface (extends `AbstractView`).
- `\Charcoal\View\LoaderInterface`
- `\Charcoal\View\ViewableInterface`
	- Viewable objects have a view, and therefore can be rendered.
- `\Charcoal\View\ViewableTrait`
	- A default (abstract) implementation, as trait, of the ViewableInterface.
- `\Charcoal\View\ViewConfig`
	- View configuration.
	- Inherits `Charcoal\Config\AbstractConfig`, from the `charcoal-config` package.
- `\Charcoal\View\ViewInterface`
	- _Views_ are the base rendering objects. They act as the public API, exposing the same methods as the engine (`render()` and `renderTemplate()`).
- `\Charcoal\View\Mustache\GenericHelper`
	- Default mustache render helper. Helpers are global functions available to all the templates.
- `\Charcoal\View\Mustache\MustacheEngine`
	- Mustache view rendering engine (`\Charcoal\View\EngineInterface`)
- `\Charcoal\View\Mustache\MustacheLoader`
	- The mustache template loader finds a mustache template file in directories.
- `\Charcoal\View\Php\PhpEngine`
- `\Charcoal\View\Php\PhpLoader`
	- The PHP template loader finds a mustache php template file in directories and includes it (run as PHP).
- `\Charcoal\View\PhpMustache\PhpMustacheEngine`

# Development

## Coding Style

The Charcoal-View module follows the Charcoal coding-style:

- [_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md), except for
	- Method names MUST be declared in `snake_case`.
- [_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md), except for the PSR-1 requirement.q
- [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), autoloading is therefore provided by _Composer_
- [_phpDocumentor_](http://phpdoc.org/)
	- Add DocBlocks for all classes, methods, and functions;
	- For type-hinting, use `boolean` (instead of `bool`), `integer` (instead of `int`), `float` (instead of `double` or `real`);
	- Omit the `@return` tag if the method does not return anything.
- Naming conventions
	- Read the [phpcs.xml](phpcs.xml) file for all the details.

> Coding style validation / enforcement can be performed with `grunt phpcs`. An auto-fixer is also available with `grunt phpcbf`.

## Authors

- Mathieu Ducharme <mat@locomotive.ca>

## Changelog

### 0.1

_Unreleased_

- Initial release

## TODOs

- Engines should all derive a base class (AbstractEngine)
- More engines: twig, plates, etc.
