Charcoal View
=============

The View package provides an integration with [Mustache] and [Twig] for templating.

## Installation

```shell
composer require charcoal/view
```

For Charcoal projects, the service provider can be registered from your configuration file:

```json
{
    "service_providers": {
        "charcoal/view/service-provider/view": {}
    }
}
```

## Usage

It is a thin layer on top of various _rendering engines_, such as **mustache** or **twig** that can be used either as a _View_ component with any frameworks, as PSR-7 renderer for such frameworks (such as Slim) 

A `View` can be used to render any template (which can be loaded from the engine) with any object (or array, for twig) as context.

```php
use Charcoal\View\Mustache\MustacheLoader;
use Charcoal\View\Mustache\MustacheEngine;
use Charcoal\View\GenericView;

$loader = new MustacheLoader([
    'base_path' => __DIR__,
    'paths'     => [
        'templates',
        'views',
    ],
]);

$engine = new MustacheEngine([
    'loader' => $loader,
]);

$view = new GenericView([
    'engine' => $engine,
]);

echo $view->render('foo/bar/template', $context);

// A template string can also be used directly, with `renderTemplate()`
$str = 'My name is {{what}}';
echo $view->renderTemplate($str, $context);
```

### Basic Usage, with service provider

All this bootstrapping code can be avoided by using the `ViewServiceProvider`. This provider expects a `config` object

```php
use Pimple\Container;
use Charcoal\View\ViewServiceProvider;

$container = new Container([
    'base_path' => __DIR__,
    'view'      => [
        'default_engine' => 'mustache',
        'paths'          => [
            'views',
            'templates',
        ],
    ],
]);
$container->register(new ViewServiceProvider());

echo $container['view']->render('foo/bar/template', $context);
```

> 👉 The default view engine, used in those examples, would be _mustache_.

### Using the Renderer, with Slim

A view can also be implicitely used as a rendering service. Using the provided `view/renderer`, with a PSR7 framework (in this example, Slim 3):

```php
use Charcoal\View\ViewServiceProvider;
use Slim\App;

$app = new App();
$container = $app->getContainer();
$container->register(new ViewServiceProvider());

$app->get('/hello/{name}', function ($request, $response, $args) {
    // This will render the "hello" template
    return $this->renderer->render($response, 'hello', $args);
});

$app->run();
```

> Just like the view, it is possible to simply register all dependencies on a Pimple container (with the `ViewServiceProvider`) to avoid all this bootstrapping code. The renderer is available as `$container['view/renderer']`.

## Module components

The basic components in the View package are:

* [**View**](#views), which provide the basic interface to all components.
* [**Engine**](#view-engines), to actually render the templates.
* [**Loader**](#loader), to load _template files_.
* [**Viewable**](#viewable-interface-and-trait), which allow any object to be rendered with a _View_.
* **Renderer**, an extra helper to use a view to render into PSR-7 request/response objects.

### Views

The `Charcoal\View\ViewInterface` defines all that is needed to render templates via a view engine:

* `render($templateIdent = null, $context = null)`
* `renderTemplate($templateString, $context = null)`

The abstract class `Charcoal\View\AbstractView` fully implements the `ViewInterface` and adds the methods:

#### Generic view

As convenience, the `\Charcoal\View\GenericView` class implements the full interface by extending the `AbstractView` base class.

### View Engines

Charcoal _views_ support different templating Engines_, which are responsible for loading the appropriate template (through a _loader_) and render a template with a given context according to its internal rules. Every view engines should implement `\Charcoal\View\EngineInterface`.

There are 3 engines available by default:

* `mustache` (**default**)
* `php`
* `twig`

#### Mustache Helpers

Mustache can be extended with the help of `helpers`. Those helpers can be set by extending `view/mustache/helpers` in the container:

```php
$container->extend('view/mustache/helpers', function(array $helpers, Container $container) {
    return array_merge($helpers, [
        'my_extended_method' => function($text, LambdaHelper $lambda) {
            if (isset($helper)) {
                $text = $helper->render($text);
            }
            return customMethod($text);
        },
    ]);
});
```

**Provided helpers:**

* **Assets** helpers:
  * `purgeJs`
  * `addJs`
  * `js`
  * `addJsRequirement`
  * `jsRequirements`
  * `addCss`
  * `purgeCss`
  * `css`
  * `addCssRequirement`
  * `cssRequirements`
  * `purgeAssets`
* **Translator** helpers:
  * `_t` Translate a string with `{{#_t}}String to translate{{/_t}}`
* **Markdown** helpers:
  * `markdown` Parse markdown to HTML with `{{#markdown}}# this is a H1{{/markdown}}`
 
#### Twig Helpers

Twig can be extended with the help of [TwigExtension](https://twig.symfony.com/doc/3.x/advanced.html#creating-an-extension). Those helpers can be set by extending `view/twig/helpers` in the container:

```php
$container['my/twig/helper'] = function (Container $container): MyTwigHelper {
    return new MyTwigHelper();
};

$container->extend('view/twig/helpers', function (array $helpers, Container $container): array {
    return array_merge(
        $helpers,
        $container['my/twig/helper']->toArray(),
    );
});
```

**Provided helpers:**

* **Debug** helpers
  * `debug` function `{{ debug() }}`
  * `isDebug` function alias of `debug`
* **Translator** helpers:
  * `trans` filter a string with `{{ "String to translate"|trans }}`
  * `transChoice` filter:
    ```
        {{ '{0}First: %test%|{1}Second: %test%'|transChoice(0, {'%test%': 'this is a test'}) }}
        {# First: this is a test #}
        {{ '{0}First: %test%|{1}Second: %test%'|transChoice(1, {'%test%': 'this is a test'}) }}
        {# Second: this is a test #}
    ```
* **Url** helpers: 
  * `baseUrl` function `{{ baseUrl() }}`
  * `siteUrl` function alias of `baseUrl`
  * `withBaseUrl` function `{{ withBaseUrl('/example/path') }}`

### Loaders

A `Loader` service is attached to every engine. Its function is to load a given template content

#### Templates

Templates are simply files, stored on the filesystem, containing the main view (typically, HTML code + templating tags, but can be kind of text data).

* For the *mustache* engine, they are `.mustache` files.
* For the *php* engine, they are `.php` files.
* For the *twig* engine, they are `.twig` files.

Templates are loaded with template _loaders_. Loaders implement the `Charcoal\View\LoaderInterface` and simply tries to match an identifier (passed as argument to the `load()` method) to a file on the filesystem.

Calling `$view->render($templateIdent, $context)` will automatically use the engine's `Loader` object to find the template `$templateIdent`.

Otherwise, calling `$view->renderTemplate($templateString, $context)` expects an already-loaded template string as parameter.

### Viewable Interface and Trait

Any objects can be made renderable (viewable) by implementing the `Charcoal\View\ViewableInterface` by using the `Charcoal\View\ViewableTrait`.

The interface adds the following methods to their implementing objects:

* `setTemplateIdent($ident)`
* `templateIdent()`
* `setView($view)`
* `view()`
* `render($templateIdent = null)`
* `renderTemplate($templateString)`

#### Examples

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

### View Service Provider

As seen in the various examples above, it is recommended to use the `ViewServiceProvider` to set up the various dependencies, according to a `config`, on a `Pimple` container.

The Service Provider adds the following service to a container:

* `view` The base view instance.
* `view/renderer` A PSR-7 view renderer.
* `view/parsedown` A parsedown service, to render markdown into HTML.

Other services / options are:

* `view/config` View configuration options.
* `view/engine` Currently used view engine.
* `view/loader` Currently used template loader.

The `ViewServiceProvider` expects the following services / keys to be set on the container:

* `config` Application configuration. Should contain a "view" key to build the _ViewConfig_ obejct.

#### The View Config

Most service options can be set dynamically from a configuration object (available in `$container['view/config']`).

**Example for Mustache:**

```json
{
    "base_path":"/",
    "view": {
        "default_engine":"mustache",
        "paths":[
            "templates",
            "views"
        ]
    }
}
```

**Example for Twig:**

```json
{
    "view": {
        "default_engine": "twig",
        "use_cache": false,
        "strict_variables": true,
        "paths": [
            "templates",
            "views"
        ]
    }
}
```

## Resources

* [Contributing](https://github.com/charcoalphp/.github/blob/main/CONTRIBUTING.md)
* [Report issues](https://github.com/charcoalphp/charcoal/issues) and
  [send pull requests](https://github.com/charcoalphp/charcoal/pulls)
  in the [main Charcoal repository](https://github.com/charcoalphp/charcoal)

[Mustache]: https://github.com/bobthecow/mustache.php
[Twig]:     https://github.com/twigphp/Twig
