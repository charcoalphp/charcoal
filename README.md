Charcoal View
=============

## Basics

The `Charcoal\View` module provides everything needed to render templates and add renderer to objects.

The `Charcoal\View\ViewInterface` defines all that is needed to render templates via a view engine:
- `render($template = null, $context = null)`
- `render_template($template_ident, $context = null)`

The abstract class `Charcoal\View\AbstractView` fully implements the `ViewInterface` and adds the methods:
- `set_engine($engine)`
- `engine()`

### Generic view
As convenience, the `\Charcoal\View\GenericView` class implements the full interface by extending the `AbstractView` base class.

```php
use \Charcoal\View\GenericView;

// Using with a loader / template ident
$view = new GenericView([
  'engine_type'=>'mustache'
]);
$context = new \Foo\Bar\ModelController();
echo $view->render_template('example/foo/bar', $context);

// Using with a template string, directly
$view = new GenericView([
  'engine_type'=>'mustache'
]);
$template = '<p>Hello {{world}}</p>';
$context = [
  'world'=>'World!'
];
echo $view->render($template, $context);
```

## View Engines
Charcoal _views_ support different templating engines, which are responsible for loading the appropriate template (through a loader) and render a template with a given context according to its internal rules. Every view engines should implement `\Charcoal\View\EngineInterface`.

There are 3 engines available by default:
- `mustache` (default)
- `php`
- `php-mustache`, the mustache templating engine with a PHP template loader. (Files are parsed with PHP before being used as a mustache templates).

### Templates
Templates are simply files, stored on the filesystem, containing the main view (typically, HTML code + templating tags, but can be kind of text data). For the mustache engine, they are `.mustache` files. for the php and php-mustache engines, they are `.php` files.

Templates are loaded with template loaders. Loaders implement the `Charcoal\View\LoaderInterface` and simply tries to match an identifier (passed as argument to the `load()` method) to a file on the filesystem.

## Viewable Interface and Trait
Any objects can be made renderable (viewable) by implementing the `Charcoal\View\ViewableInterface` by using the `Charcoal\View\ViewableTrait`. 

The interface adds the following methods:
- `set_template_engine($engine)`
- `template_engine()`
- `set_template_ident($ident)`
- `template_ident()`
- `set_view($view)`
- `view()`
- `display($template = null)`
- `render($template = null)`
- `render_template($template_ident)`

The viewable trait also adds the following abstract methods:
- `create_view` (**abstract** / _private_)

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

## Classes
- \Charcoal\View\AbstractView
- \Charcoal\View\EngineInterface
- \Charcoal\View\LoaderInterface
- \Charcoal\View\ViewableInterface
- \Charcoal\View\ViewableTrait
- \Charcoal\View\ViewInterface
- \Charcoal\View\Mustache\MustacheEngine
- \Charcoal\View\Mustache\MustacheLoader
- \Charcoal\View\Php\PhpEngine
- \Charcoal\View\Php\PhpLoader
- \Charcoal\View\PhpMustache\PhpMustacheEngine
