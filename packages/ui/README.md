Charcoal UI
===========

The `Charcoal\Ui` module provides tools to create UI elements (dashboards, layouts, forms and menus) from simple metadata / config.

# Table of contents

- [How to install](#how-to-install)
    -   [Dependencies](#dependencies)
- [Base UI Item](#base-ui-item)
- [Dashboard](#dashboard)
- [Layout](#layout)
- [Form](#form)
- [Form Group](#form-group)
- [Form Input](#form-input)
- [Menu](#menu)
- [Menu Item](#menu-item)
- [Service Providers](#service-provider)
- [Development](#development)
    - [Development dependencies](#development-dependencies)
    - [Coding Style](#coding-style)
    - [Authors](#authors)
    - [Changelog](#changelog)
- [Report Issues](#report-issues)
- [Contribute](#contribute)

# How to install

The preferred (and only supported) way of installing charcoal-ui is with **composer**:

```shell
$ composer require locomotivemtl/charcoal-ui
```

## Dependencies

-   PHP 7.1+
-   [`psr/log`](http://www.php-fig.org/psr/psr-3/)
    - A PSR-3 compliant logger should be provided to the various services / classes.
-   [`locomotivemtl/charcoal-config`](https://github.com/locomotivemtl/charcoal-config) 0.6+
    - The UI objects are _configurable_ with various configs.
-   [`locomotivemtl/charcoal-translation`](https://github.com/locomotivemtl/charcoal-translation)
    - To provide l10n to the UI objects.
-   [`locomotivemtl/charcoal-view`](https://github.com/locomotivemtl/charcoal-view) 0.1+
    - The base `UiItem` are `Viewable`, meaning they can be rendered with a `View`.

# Example Usage

## Form

```php
use Charcoal\Config\GenericMetadata;
use Charcoal\Ui\Form\FormBuilder;
use Charcoal\Ui\Form\FormFactory;

$metadata = new GenericMetadata([
    'properties' => [
        'first_name' => [
            'type' => 'string',
        ],
        'last_name' => [
            'type' => 'string',
        ],
        'email' => [
            'type' => 'email',
        ],
    ],
]);

$formData = [
    'first_name' => 'Mathieu',
    'last_name'  => 'Ducharme',
    'email'      => 'mat@locomotive.ca',
];

$formConfig = [
    'type'           => 'charcoal/ui/form/generic'
    'template_ident' => 'foo/bar/form',
    'template_data'  => [],
    'label'          => 'Example Form',
    'groups'         => [
        'info' => [
            'layout' => [
                'structure' => [
                    'columns' => [
                        [ 1, 1 ],
                        [ 1 ],
                    ],
                ],
            ],
            'properties' => [
                'first_name',
                'last_name',
                'email',
            ],
        ],
    ],
];

$formBuilder = new FormBuilder([
    'form_factory' => new FormFactory(),
    'view'         => $container['view'],
]);

$form = $formBuilder->build($formConfig);
$form->setMetadata($metadata);
$form->setFormData($formData);

echo $form->render();
```

# Base UI Item

All UI classes implements the same basic class: `\Charcoal\Ui\UiItemInterface`. This interface defines a basic set of properties that are shared across (almost) all ui item types.

## Base UI Item config

| Key                | Type        | Default     | Description |
| ------------------ | ----------- | ----------- | ----------- |
| **type**           | `string`    |
| **title**          | `string[1]` |
| **subtitle**       | `string[1]` |
| **description**    | `string[1]` |
| **notes**          | `string[1]` |
| **template_ident** | `string`    | `''`        | The default view template. |

<small><sup>[1]</sup> indicates a l10n string (`TranslationString`) </small>

## View Integration

The `UiItemInterface` is a _Viewable_ item; that means it also implements the `\Charcoal\View\ViewableInterface`. The `AbstractUiItem` fully implements this interface by using `\Charcoal\View\ViewableTrait`.

_Viewable_ objects can set a _View_ object with `setView($view)` have a `template_ident` (which can be set with `setTemplateIdent($id)`). See the [charcoal-view](https://github.com/locomotivemtl/charcoal-view) module for details.

The easiest way to use a View is by setting a `ViewInterface` object as `view` service on a DI container / Service Provider.

# Dashboard

Dashboards define a _layout_ of _widgets_.

 -   `layout` is a `LayoutInterface` object that can be created with a `LayoutBuilder`.
 -   `widgets` is a collection of any `UiItemInterface` objects.
    - Any  PHP class can actually be a "widget", but base widgets are provided as convenience.

## Dashboard config

| Key         | Type           | Default     | Description |
| ----------- | -------------- | ----------- | ----------- |
| **type**    | `string`       |
| **layout**  | `LayoutConfig` |
| **widgets** | `array`        |


## Dashboard dependencies

-   `logger`
-   `view`
-   `widget_factory`

## Dashboard API

-   `setLayout()`
-   `layout()`
-   `setWidgets(array $widgets)`
-   `widgets()`
-   `addWidget()`
-   `numWidgets()`
-   `hasWidget()`

# Layout

Layouts define a grid (column-based) structure.

## Layout config

| Key                   | Type     | Default     | Description |
| --------------------- | -------- | ----------- | ----------- |
| **structure**         | `array`  |
| **structure.columns** | `array`  |

**Example layout JSON config**

```json
"layout": {
    "structure": [
        { "columns": [ 2, 1 ] },
        { "columns": [ 1 ] },
        { "columns": [ 1 ] }
    ]
}
```

## Layout API

-   `setStructure(array $layouts)`
-   `structure()`
-   `numRows()`
-   `rowIndex($position = null)`
-   `rowData($position = null)`
-   `rowNumColumns($position = null)`
-   `rowNumCells($position = null)`
-   `rowFirstCellIndex($position = null)`
-   `cellRowIndex($position = null)`
-   `numCellsTotal()`
-   `cellSpan($position = null)`
-   `cellSpanBy12($position = null)`
-   `cellStartsRow($position = null)`
-   `cellEndsRow($position = null)`
-   `start()`
-   `end()`

## Layout Aware objects

In the `charcoal-ui` module, 3 base objects use a layout: _dashboards_, _forms_ and _form groups_.

Those classes implement the Layout requirement by implementing the `\Charcoal\Ui\Layout\LayoutAwareInterface` with the use of its corresponding `LayoutAwareTrait`.

# Form

Forms define a layout of form groups, form options, data and metadata.

-   Forms have [_groups_](#form-group), which have [_inputs_](#form-input).
-   Groups can be layouted with a `layout` object.
-   Form can be pre-populated with _form data_.
-   _Metadata_ ca

## Form config

| Key           | Type            | Default     | Description |
| ------------- | --------------- | ----------- | ----------- |
| **type**      | `string`        |
| **action**    | `string`        | `''`        | URL where the form will be submitted. |
| **method**    | `string`        | `'post'`    | HTTP method to submit ("post" or "get"). |
| **layout**    | `LayoutConfig`  |
| **groups**    | `FormGroupConfig[]` |
| **form_data** | `array`         |
| **metadata**  | `array`         |


## Form dependencies

-   `view`
-   `group_factory`

## Form API

-   `setAction($action)`
-   `action()`
-   `setMethod($method)`
-   `method()`
-   `setGroups(array $groups)`
-   `groups()`
-   `addGroup($groupIdent, $groupData)`
-   `numGroups()`
-   `hasGroups()`
-   `setFormData(array $formData)`
-   `formData()`
-   `addFormData()`

# Form Group

## Form group config

| Key                     | Type           | Default     | Description |
| ----------------------- | -------------- | ----------- | ----------- |
| **form**                |
| **template_ident**      | `string`       |
| **template_controller** | `string`       |
| **priority**            | `int`          |
| **layout**              | `LayoutConfig` |
| **properties**          | `array`        |

## Form group API

-   `setForm($form)`
-   `setInputs(array $groups)`
-   `inputs()`
-   `addInput($inputIdent, $inputData)`
-   `numInputs()`
-   `hasInputs()`

# Form Input

-   `form`
-   `label`
-   `property_ident`
-   `template_ident`
-   `template_data`
-   `read_only`
-   `required`
-   `disabled`
-   `multiple`
-   `input_id`
-   `input_name`

# Menu

# Menu Item

Menu items define a menu level (ident, label and url) and its children (array of Menu Item).

## Menu item config

-   `ident`
-   `icon_ident`
-   `label`
-   `url`
-   `children`

## Menu item API

-   `setIdent($ident)`
-   `ident()`
-   `setLabel($label)`
-   `label()`
-   `setUrl($url)`
-   `url()`
-   `setChildren($children)`
-   `children()`
-   `numChildren()`
-   `hasChildren()`

# Creational Helpers

Most UI elements are very dynamic. The types of object to create is often read from a string in a configuration object. Therefore, factories are the preferred way of instanciating new UI items.

Ui items have also many inter-connected dependencies. Builders should therefore be used for object creation / instanciation. They use a factory internally, and have a `build($opts)` methods that allow to retrieve class name from a dynamic source, do initialization, dpendencies management and more. Builders require `Pimple` for a DI container.

## Factories

-   `\Charcoal\Ui\Dashboard\DashboardFactory`
-   `\Charcoal\Ui\Layout\LayoutFactory`
-   `\Charcoal\Ui\Form\FormFactory`
-   `\Charcoal\Ui\FormGroup\FormGroupFactory`
-   `\Charcoal\Ui\FormInput\FormInputFactory`
-   `\Charcoal\Ui\Menu\MenuFactory`
-   `\Charcoal\Ui\MenuItem\MenuItemFactory`

## Builders

-   `\Charcoal\Ui\Dashboard\DashboardBuilder`
-   `\Charcoal\Ui\Layout\LayoutBuilder`
-   `\Charcoal\Ui\Form\FormBuilder`
-   `\Charcoal\Ui\FormGroup\FormGroupBuilder`
-   `\Charcoal\Ui\FormInput\FormInputBuilder`
-   `\Charcoal\Ui\Menu\MenuBuilder`
-   `\Charcoal\Ui\MenuItem\MenuItemBuilder`

# Service Providers

Service providers are provided in the `Charcoal\Ui\ServiceProvider` namespace for for convenience. They are the recommended way of using `charcoal-ui`, as they register all the creational utilities to a container, taking care of dependencies.

-   `\Charcoal\Ui\ServiceProvider\DashboardServiceProvider`
    - `dashboard/factory`
    - `dashboard/builder`
-   `\Charcoal\Ui\ServiceProvider\FormServiceProvider`
    - `form/factory`
    - `form/builder`
    - `form/group/factory`
    - `form/input/factory`
    - `form/input/builder`
-   `\Charcoal\Ui\ServiceProvider\LayoutServiceProvider`
    - `layout/factory`
    - `layout/builder`
-   `\Charcoal\Ui\ServiceProvider\MenuServiceProvider`
    - `menu/factory`
    - `menu/builder`
    - `menu/item/factory`
    - `menu/item/builder`
-   `\Charcoal\Ui\ServiceProvider\UiServiceProvider`
    - Register all the other service providers (dashboard, form, layout and menu).

## Required services

There are a few dependencies on external services, that should be set on the same DI container as the one passed to the service providers:

-   `logger`, a PSR-3 logger instance.
    - Typically a `monolog` instance from `charcoal-app`.
-   `view`, a `\Charcoal\View\ViewInterface` instance.
    - Typically provided with `\Charcoal\App\Provider\ViewServiceProvider`.

# Development

To install the development environment:

```shell
$ composer install --prefer-source
```

## API documentation

-   The auto-generated `phpDocumentor` API documentation is available at [https://locomotivemtl.github.io/charcoal-ui/docs/master/](https://locomotivemtl.github.io/charcoal-ui/docs/master/)
-   The auto-generated `apigen` API documentation is available at [https://codedoc.pub/locomotivemtl/charcoal-ui/master/](https://codedoc.pub/locomotivemtl/charcoal-ui/master/index.html)

## Development dependencies

-   `phpunit/phpunit`
-   `squizlabs/php_codesniffer`
-   `satooshi/php-coveralls`

## Continuous Integration

| Service | Badge | Description |
| ------- | ----- | ----------- |
| [Travis](https://travis-ci.org/locomotivemtl/charcoal-ui) | [![Build Status](https://travis-ci.org/locomotivemtl/charcoal-ui.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-ui) | Runs code sniff check and unit tests. Auto-generates API documentation. |
| [Scrutinizer](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-ui/) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-ui/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-ui/?branch=master) | Code quality checker. Also validates API documentation quality. |
| [Coveralls](https://coveralls.io/github/locomotivemtl/charcoal-ui) | [![Coverage Status](https://coveralls.io/repos/github/locomotivemtl/charcoal-ui/badge.svg?branch=master)](https://coveralls.io/github/locomotivemtl/charcoal-ui?branch=master) | Unit Tests code coverage. |
| [Sensiolabs](https://insight.sensiolabs.com/projects/533b5796-7e69-42a7-a046-71342146308a) | [![SensioLabsInsight](https://insight.sensiolabs.com/projects/ad5d1699-07cc-45b5-9ba4-9b3b45f677e0/mini.png)](https://insight.sensiolabs.com/projects/ad5d1699-07cc-45b5-9ba4-9b3b45f677e0) | Another code quality checker, focused on PHP. |

## Coding Style

The Charcoal-Ui module follows the Charcoal coding-style:

-   [_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
-   [_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
-   [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), autoloading is therefore provided by _Composer_.
-   [_phpDocumentor_](http://phpdoc.org/) comments.
-   Read the [phpcs.xml](phpcs.xml) file for all the details on code style.

> Coding style validation / enforcement can be performed with `composer phpcs`. An auto-fixer is also available with `composer phpcbf`.

# Authors

-   Mathieu Ducharme <mat@locomotive.ca>

# License

Charcoal is licensed under the MIT license. See [LICENSE](LICENSE) for details.



## Report Issues

In case you are experiencing a bug or want to request a new feature head over to the [Charcoal monorepo issue tracker](https://github.com/charcoalphp/charcoal/issues)



## Contribute

The sources of this package are contained in the Charcoal monorepo. We welcome contributions for this package on [charcoalphp/charcoal](https://github.com/charcoalphp/charcoal).
