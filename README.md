Charcoal View
=============

The `Charcoal\View` module (`locomotivemtl/charcoal-view`) provides everything needed to render templates and add renderer to objects.

It is a thin layer on top of various _rendering engines_, such as **mustache** or **twig** that can be used either as a _View_ component with any frameworks, as PSR-7 renderer for such frameworks (such as Slim) 

It is the default view layer for `charcoal-app` projects.

[![Build Status](https://travis-ci.org/locomotivemtl/charcoal-view.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-view)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/396d2f06-82ba-4c79-b8cc-762f1e8bda29/mini.png)](https://insight.sensiolabs.com/projects/396d2f06-82ba-4c79-b8cc-762f1e8bda29)

# Table of content

-   [How to install](#how-to-install)
    -   [Dependencies](#dependencies)
-   [Basic Usage](#basic-usage)
    -   [Using the Renderer, with Slim](#using-the-renderer-with-slim)   
-   [Module components](#module-components)
    -   [Views](#views)
        -   [Generic View](#generic-view)
    -   [View Engines](#view-engines)
        - [Mustache Helpers](#mustache-helpers)
    -   [Loaders](#loaders)
        -   [Templates](#templates)
    -   [Viewable Interface and Trait](#viewable-interface-and-trait)
    -   [View Service Provider](#view-service-provider)
-   [Development](#development)
    -   [Development dependencies](#development-dependencies)
    -   [Coding Style](#coding-style)
    -   [Authors](#authors)

# How to install

The preferred (and only supported) way of installing charcoal-view is with **composer**:

```shell
$ composer require locomotivemtl/charcoal-view
```
To install a full Charcoal project, which includes `charcoal-view`:

```shell
$ composer create-project locomotivemtl/charcoal-project-boilerplate:@dev --prefer-source
```


## Dependencies

-   `PHP 5.6+`
    -   Older versions of PHP are deprecated, therefore not supported.
    -   PHP 7 is recommended for security and performance reasons.
-   [`psr/http-message`](http://www.php-fig.org/psr/psr-7/)
    -   Charcoal View provides a PSR7 renderer.
-   [`locomotivemtl/charcoal-config`](https://github.com/locomotivemtl/charcoal-config)
    -   The view objects are _configurable_ with `\Charcoal\View\ViewConfig`.
    [`locomotivemtl/charcoal-translator`](https://github.com/locomotivemtl/charcoal-translator)
    -   The translator service
-   [`erusev/parsedown`](https://github.com/erusev/parsedown)
    -   A markdown parser, which is provided to engines or could be used as a service.

### Optional dependencies

-   [`mustache/mustache`](https://github.com/bobthecow/mustache.php)
    -   The default rendering engine is _mustache_, so it should be included in most cases.
    -   All default charcoal modules use mustache templates.
-   [`twig/twig`](http://twig.sensiolabs.org/)
    -   Twig can also be used as a rendering engine for the view.
-   [`pimple/pimple`](http://pimple.sensiolabs.org/)
    -   Dependencies management can be done with a Pimple ServiceProvider(`\Charcoal\View\ViewServiceProvider`)
    -   It is actually required by default in `charcoal-app`.

> 👉 Development dependencies are described in the _Development_ section of this README file.

# Basic Usage

A `View` can be used to render any template (which can be loaded from the engine) with any object (or array, for twig) as context.

```php
use Charcoal\View\Mustache\MustacheLoader;
use Charcoal\View\Mustache\MustacheEngine;
use Charcoal\View\GenericView;

$loader = new MustacheLoader([
    'base_path' => __DIR__,
    'paths'     => [
        'templates',
        'views
    ]
]);

$engine = new MustacheEngine([
    'loader' => $loader
]);

$view = new GenericView([
    'engine'  => $engine
]);

echo $view->render('foo/bar/template', $context);

// A template string can also be used directly, with `renderTemplate()`
$str = 'My name is {{what}}';
echo $view->renderTemplate($str, $context);
```

## Basic Usage, with service provider

All this bootstrapping code can be avoided by using the `ViewServiceProvider`. This provider expects a `config` object

```php
use Pimple\Container;
use Charcoal\View\ViewServiceProvider;

$container = new Container([
    'base_path' => __DIR__,
    'view' = [
        'default_engine' -> 'mustache',
        'paths' => [
            'views',
            'templates'
        ]
    ]
]);
$container->register(new ViewServiceProvider());

echo $container['view']->render('foo/bar/template', $context);
```

> 👉 The default view engine, used in those examples, would be _mustache_.

## Using the Renderer, with Slim

A view can also be implicitely used as a rendering service. Using the provided `view/renderer`, with a PSR7 framework (in this example, Slim 3):

```php
use Charcoal\View\ViewServiceProvider;

include 'vendor/autoload.php';

$app = new \Slim\App();
$container = $app->getContainer();
$container->register(new ServiceProvider());

$app->get('/hello/{name}', function ($request, $response, $args) {
    // This will render the "hello" template
    return $this->renderer->render($response, 'hello', $args);
});

$app->run();
```

> Just like the view, it is possible to simply register all dependencies on a Pimple container (with the `ViewServiceProvider`) to avoid all this bootstrapping code. The renderer is available as `$container['view/renderer']`.

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

## View Engines

Charcoal _views_ support different templating Engines_, which are responsible for loading the appropriate template (through a _loader_) and render a template with a given context according to its internal rules. Every view engines should implement `\Charcoal\View\EngineInterface`.

There are 3 engines available by default:

-   `mustache` (**default**)
-   `php`
-   `twig`

### Mustache Helpers

Mustache can be extended with the help of `helpers`. Those helpers can be set by extending `view/mustache/helpers` in the container:

```
$container->extend('view/mustache/helpers', function(array $helpers, Container $container) {
    return array_merge($helpers, [
        'my_extended_method' => function($text, LambdaHelper $lambda) {
            if (isset($helper)) {
                $text = $helper->render($text);
            }
            return customMethod($text);
        }
    ]);
});
```

*Provided helpers:*

- **Assets** helpers:
    - `purgeJs`
    - `addJs`
    - `js`
    - `addJsRequirement`
    - `jsRequirements`
    - `addCss`
    - `purgeCss`
    - `css`
    - `addCssRequirement`
    - `cssRequirements`
    - `purgeAssets`
- **Translator** helpers:
    - `_t` Translate a string with `{{#_t}}String to translate{{/_t}}`
- **Markdown** helpers:
    - `markdown` Parse markdown to HTML with `{{#markdown}}# this is a H1{{/markdown}}`
 

## Loaders

A `Loader` service is attached to every engine. Its function is to load a given template content

### Templates

Templates are simply files, stored on the filesystem, containing the main view (typically, HTML code + templating tags, but can be kind of text data).

-   For the *mustache* engine, they are `.mustache` files.
-   For the *php* engine, they are `.php` files.

Templates are loaded with template _loaders_. Loaders implement the `Charcoal\View\LoaderInterface` and simply tries to match an identifier (passed as argument to the `load()` method) to a file on the filesystem.

Calling `$view->render($templateIdent, $context)` will automatically use the engine's `Loader` object to find the template `$templateIdent`.

Otherwise, calling `$view->renderTemplate($templateString, $context)` expects an already-loaded template string as parameter.

## Viewable Interface and Trait

Any objects can be made renderable (viewable) by implementing the `Charcoal\View\ViewableInterface` by using the `Charcoal\View\ViewableTrait`.

The interface adds the following methods to their implementing objects:

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

## View Service Provider

As seen in the various examples above, it is recommended to use the `ViewServiceProvider` to set up the various dependencies, according to a `config`, on a `Pimple` container.

The Service Provider adds the following service to a container:

- `view` The base view instance.
- `view/renderer` A PSR-7 view renderer.
- `view/parsedown` A parsedown service, to render markdown into HTML.

Other services / options are:

- `view/config` View configuration options.
- `view/engine` Currently used view engine.
- `view/loader` Currently used template loader.

The `ViewServiceProvider` expects the following services / keys to be set on the container:

- `config` Application configuration. Should contain a "view" key to build the _ViewConfig_ obejct.

### The View Config

Most service options can be set dynamically from a configuration object (available in `$container['view/config']`).

Example:

```json
{
    "base_path":"/",
    "view": {
        "engine":"mustache",
        "paths":[
            "templates",
            "views"
        ]
    }
}
```

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
-   `pimple/pimple`
-   `mustache/mustache`
-   `twig/twig`

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

# Authors

-   Mathieu Ducharme <mat@locomotive.ca>
-   [Locomotive](https://locomotive.ca)

# License

Charcoal is licensed under the MIT license. See [LICENSE](LICENSE) for details.
