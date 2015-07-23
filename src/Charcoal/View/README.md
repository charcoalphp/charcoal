Charcoal View
=============

The `Charcoal\View` namespace of the `charcoal-core` module contains all classes and interfaces pertaining to _views_.

> The classes and interfaces in this namespace are very low-level and are mostly intended to be used through higher-level children class. For examples `\Charcoal\Model\ModelView` and `\Charcoal\Property\PropertyView`, as well as `\Charcoal\Template\Template` and `Charcoal\Widget\Widget` from the `charcoal-base` module.

# Available engines

View engines implements the `ViewEngineInterface` interface by extending the `AbstractViewEngine` class. They define how the view template is loaded and generated.

The available engines are:
- `mustache`: The default engine. Files are expected to be 100% mustache and have the `.mustache` extension.
- `php`: No mustache. Files are expected to be 100% pure PHP to generate the template
- `php-mustache`: Files are first passed through the PHP interpretor and then to the `mustache` engine

# Usage

```php
// Expectation of class `MyView extends AbstractView` and `MyViewControllor extends AbstractViewController`
// Expection of an instanciated `$model` which implements `ViewableInterface`
$view = new MyView();
$controller = new MyViewController();
$template = 'Hello {{test}}';
$view->render($template, $controller);
```

# Dependencies

PHP dependencies should be handled with composer, they are:
- `mustache/mustache` for the `\Mustache\*` classes.

Intra-dependencies (dependencides on other part of the `charcoal-core` module) are:
- `Charcoal\Charcoal` for the logger.
- `Charcoal\Loader\FileLoader` for the template loading.

## About Mustache...
