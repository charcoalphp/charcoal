Charcoal View
=============

The `Charcoal\View` module provides everything needed to render templates and add renderer to objects.

It is a thin layer on top of various _rendering engines_, such as **mustache** or **twig**.

It is the default view layer for `charcoal-app` projects.

[![Build Status](https://travis-ci.org/locomotivemtl/charcoal-view.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-view)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/396d2f06-82ba-4c79-b8cc-762f1e8bda29/mini.png)](https://insight.sensiolabs.com/projects/396d2f06-82ba-4c79-b8cc-762f1e8bda29)

# Table of content

-   [How to install](#how-to-install)
    -   [Dependencies](#dependencies)
-   [Usage](#usage)
-   [Module components](#module-components)
    -   Views
        -   Generic View
    -   View Engines
    -   Loaders
        -   Templates
    -   Viewable Interface and Trait
-   [Development](#development)
    -   [Development dependencies](#development-dependencies)
    -   [Coding Style](#coding-style)
    -   [Authors](#authors)
    -   [Changelog](#changelog)

# How to install

The preferred (and only supported) way of installing charcoal-view is with **composer**:

```shell
$ composer require locomotivemtl/charcoal-view
```

## Dependencies

-   `PHP 5.5+`
    -   Older versions of PHP are deprecated, therefore not supported.
-   [`psr/log`](http://www.php-fig.org/psr/psr-3/)
    -   A PSR-3 compliant logger should be provided to the various services / classes.
-   [`psr/http-message`](http://www.php-fig.org/psr/psr-7/)
    -   Charcoal View provides a PSR7 renderer.
-   [`locomotivemtl/charcoal-config`](https://github.com/locomotivemtl/charcoal-config)
    -   The view objects are _configurable_ with `\Charcoal\View\ViewConfig`.

### Optional dependencies

-   [`mustache/mustache`](https://github.com/bobthecow/mustache.php)
    -   The default rendering engine is _mustache_, so it should be included in most cases.
    -   All default charcoal modules use mustache templates.
-   [`twig/twig`](http://twig.sensiolabs.org/)
    -   Twig can also be used as a rendering engine for the view.

> 👉 Development dependencies are described in the _Development_ section of this README file.

# Usage

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

# Module components

The basic components in `charcoal-view` are:

-   [**View**](#views), which provide the basic interface to all components.
-   [**Engine**](#view-engines), to actually render the templates.
-   [**Loader**](#loader), to load _template files_.
-   [**Viewable**](#viewable-interface-and-trait), which allow any object to be rendered with a _View_.
-   **Renderer**, an extra helper to use a view to render into PSR-7 request/response objects.

## Views

The `Charcoal\View\ViewInterface` defines all that is needed to render templates via a view engine:

-   `render($templateIdent = null, $context = null)`
-   `renderTemplate($templateString, $context = null)`

The abstract class `Charcoal\View\AbstractView` fully implements the `ViewInterface` and adds the methods:

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

There are 4 engines available by default:

-   `mustache` (**default**)
-   `php`
-   `php-mustache`, the mustache templating engine with a PHP template loader. (Files are parsed with PHP before being used as a mustache templates).
-   `twig`

## Loaders

### Templates

Templates are simply files, stored on the filesystem, containing the main view (typically, HTML code + templating tags, but can be kind of text data).

-   For the *mustache* engine, they are `.mustache` files.
-   For the *php* and *php-mustache* engines, they are `.php` files.

Templates are loaded with template _loaders_. Loaders implement the `Charcoal\View\LoaderInterface` and simply tries to match an identifier (passed as argument to the `load()` method) to a file on the filesystem.

Calling `$view->render($templateIdent, $ctx)` will automatically use the engine's `Loader` object to find the template `$templateIdent`.

Otherwise, calling `$view->renderTemplate($templateString, $ctx)` expects an already-loaded template string as parameter.

## Viewable Interface and Trait

Any objects can be made renderable (viewable) by implementing the `Charcoal\View\ViewableInterface` by using the `Charcoal\View\ViewableTrait`.

The interface adds the following methods:

-   `setTemplateIdent($ident)`
-   `templateIdent()`
-   `setView($view)`
-   `view()`
-   `render($templateIdent = null)`
-   `renderTemplate($templateString)`

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
$obj->renderTemplate('Hello {{world}}');
```

would output: `"Hello world!"`

# Development

To install the development environment:

```shell
$ composer install --prefer-source
```

Run tests with

```shell
$ composer test
```

## API documentation

-   The auto-generated `phpDocumentor` API documentation is available at [https://locomotivemtl.github.io/charcoal-view/docs/master/](https://locomotivemtl.github.io/charcoal-view/docs/master/)
-   The auto-generated `apigen` API documentation is available at [https://codedoc.pub/locomotivemtl/charcoal-view/master/](https://codedoc.pub/locomotivemtl/charcoal-view/master/index.html)

## Development dependencies

-   `phpunit/phpunit`
-   `squizlabs/php_codesniffer`
-   `satooshi/php-coveralls`

## Continuous Integration

| Service | Badge | Description |
| ------- | ----- | ----------- |
| [Travis](https://travis-ci.org/locomotivemtl/charcoal-view) | [![Build Status](https://travis-ci.org/locomotivemtl/charcoal-view.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-view) | Runs code sniff check and unit tests. Auto-generates API documentation. |
| [Scrutinizer](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-view/) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-view/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-view/?branch=master) | Code quality checker. Also validates API documentation quality. |
| [Coveralls](https://coveralls.io/github/locomotivemtl/charcoal-view) | [![Coverage Status](https://coveralls.io/repos/github/locomotivemtl/charcoal-view/badge.svg?branch=master)](https://coveralls.io/github/locomotivemtl/charcoal-view?branch=master) | Unit Tests code coverage. |
| [Sensiolabs](https://insight.sensiolabs.com/projects/396d2f06-82ba-4c79-b8cc-762f1e8bda29) | [![SensioLabsInsight](https://insight.sensiolabs.com/projects/396d2f06-82ba-4c79-b8cc-762f1e8bda29/mini.png)](https://insight.sensiolabs.com/projects/396d2f06-82ba-4c79-b8cc-762f1e8bda29) | Another code quality checker, focused on PHP. |

## Coding Style

The Charcoal-View module follows the Charcoal coding-style:

-   [_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
-   [_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
-   [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), autoloading is therefore provided by _Composer_.
-   [_phpDocumentor_](http://phpdoc.org/) comments.
-   Read the [phpcs.xml](phpcs.xml) file for all the details on code style.

> Coding style validation / enforcement can be performed with `composer phpcs`. An auto-fixer is also available with `composer phpcbf`.
## Authors

-   Mathieu Ducharme <mat@locomotive.ca>

## Changelog

### 0.1.1
_Released on 2016-02-22_

-   Add jsRequirements and cssRequirements to default mustache helper.

### 0.1
_Released on 2016-02-04_

-   Initial release of `charcoal-view`.

# License

**The MIT License (MIT)**

_Copyright © 2016 Locomotive inc._
> See [Authors](#authors).

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
