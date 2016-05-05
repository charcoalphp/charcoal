Charcoal UI
===========

The `Charcoal\Ui` module provides tools to create UI elements (dashboards, layouts, forms and menus) from simple metadata / config.

# Table of contents

- [How to install](#how-to-install)
	- [Dependencies](#dependencies)
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

# How to install

The preferred (and only supported) way of installing charcoal-view is with **composer**:

```shell
$ composer require locomotivemtl/charcoal-view
```

## Dependencies

- `PHP 5.5+`
	+ Older versions of PHP are deprecated, therefore not supported.
- [`psr/log`](http://www.php-fig.org/psr/psr-3/)
	+ A PSR-3 compliant logger should be provided to the various services / classes.
- [`locomotivemtl/charcoal-config`](https://github.com/locomotivemtl/charcoal-config) 0.5+
	+ The UI objects are _configurable_ with `\Charcoal\View\ViewConfig`.
- [`locomotivemtl/charcoal-translation`](https://github.com/locomotivemtl/charcoal-translation)
	+
- [`locomotivemtl/charcoal-view`](https://github.com/locomotivemtl/charcoal-view) 0.1+
	+ The base `UiItem` can be are `Viewable`, meaning they can be rendered with a `View`.

# Example Usage

## Form

```php

$metadata = new \Charcoal\Config\GenericMetadata([
	'properties' => [
		'first_name' => [
			'type' => 'string'
		],
		'last_name' => [
			'type' => 'string'
		],
		'email' => [
			'type' => 'email'
		]
	]
]);

$formData = [
	'first_name' => 'Mathieu',
	'last_name' => 'Ducharme',
	'email' => 'mat@locomotive.ca'
];

$formConfig = [
	'type' 						=> 'charcoal/ui/form/generic'
	'template_ident'	=> 'foo/bar/form',
	'template_data'		=> [],
	'label'						=> 'Example Form',
	'groups'					=> [
		'info' => [
			'layout' =>[
				'structure' => [
					'columns' => [
						[1,1],
						[1]
					]
				]
			],
			'properties' => [
				'first_name',
				'last_name',
				'email'
			]
		]
	]
];

$formBuilder = new \Charcoal\Ui\Form\FormBuilder([
	'form_factory' => new \Charcoal\Ui\Form\FormFactory(),
	'view' => $container['view']
]);

$form = $formBuilder->buid($formConfig);
$form->setMetadata($metadata);
$form->setFormData($formData);

echo $form->render();
```

# Base UI Item

All UI classes implements the same basic class: `\Charcoal\Ui\UiItemInterface`. This interface defines

## Base UI Item config

- `type`
- `title`
- `subtitle`
- `description`
- `notes`
- `template_ident`
	+ The default view template.

## View Integration

The `UiItemInterface` is a _Viewable_ item; that means it also implements the `\Charcoal\View\ViewableInterface`. The `AbstractUiItem` fully implements this interface by using `\Charcoal\View\ViewableTrait`.

_Viewable_ objects can set a _View_ object with `setView($view)` have a `template_ident` (which can be set with `setTemplateIdent($id)`). See the [charcoal-view](https://github.com/locomotivemtl/charcoal-view) module for details.

The easiest way to use a View is by setting a `ViewInterface` object as `view` service on a DI container / Service Provider.

# Dashboard

Dashboards define a _layout_ of _widgets_.

 - `layout` is a `LayoutInterface` object that can be created with a `LayoutBuilder`.
 - `widgets` is a collection of any `UiItemInterface` objects.

## Dashboard config

- `type` _string_
- `layout` _array_
- `widgets` _array_

## Dashboard dependencies

- `logger`
- `view`
- `widget_factory`

## Dashboard API

- `setLayout()`
- `layout()`
- `setWidgets(array $)`
- `widgets()`
- `addWidget()`
- `numWidgets()`
- `hasWidget()`

# Layout

Layouts define a grid (column-based) structure.

## Layout config

- `structure`
	- `columns`

## Layout API

- `setStructure(array $layouts)`
- `structure()`
- `numRows()`
- `rowIndex($position = null)`
- `rowData($position = null)`
- `rowNumColumns($position = null)`
- `rowNumCells($position = null)`
- `rowFirstCellIndex($position = null)`
- `cellRowIndex($position = null)`
- `numCellsTotal()`
- `cellSpan($position = null)`
- `cellSpanBy12($position = null)`
- `cellStartsRow($position = null)`
- `cellEndsRow($position = null)`
- `start()`
- `end()`

## Layout Aware objects

In the `charcoal-ui` module, 3 base objects use a layout: _dashboards_, _forms_ and _form groups_.

Those classes implement the Layout requirement by implementing the `\Charcoal\Ui\Layout\LayoutAwareInterface` with the use of its corresponding `LayoutAwareTrait`.

# Form

Forms define a layout of form groups, form options, data and metadata.

- Forms have [_groups_](#form-group), which have [_inputs_](#form-input).
- Groups can be layouted with a `layout` object.
- Form can be pre-populated with _form data_.
- _Metadata_ ca

## Form config

- `type`
- `action`
	+ Where the form will be sent upon submission (URL).
- `method`
	+ The http method to submit the form: "post" (default) or "get".
- `layout`
- `groups`
- `form_data`
- `metadata`

## Form dependencies

- `view`
- `group_factory`

## Form API

- `setAction($action)`
- `action()`
- `setMethod($method)`
- `method()`
- `setGroups(array $groups)`
- `groups()`
- `addGroup($groupIdent, $groupData)`
- `numGroups()`
- `hasGroups()`
- `setFormData(array $formData)`
- `formData()`
- `addFormData()`

# Form Group

## Form group config

- `form`
- `template_ident`
- `template_controller`
- `priority`
- `layout`
- `properties`

## Form group API

- `setForm($form)`
- `setInputs(array $groups)`
- `inputs()`
- `addInput($inputIdent, $inputData)`
- `numInputs()`
- `hasInputs()`

# Form Input

- `form`
- `label`
- `property_ident`
- `template_ident`
- `template_data`
- `read_only`
- `required`
- `disabled`
- `multiple`
- `input_id`
- `input_name`

# Menu

# Menu Item

Menu items define a menu level (ident, label and url) and its children (array of Menu Item).

## Menu item config

- `ident`
- `icon_ident`
- `label`
- `url`
- `children`

## Menu item API

- `setIdent($ident)`
- `ident()`
- `setLabel($label)`
- `label()`
- `setUrl($url)`
- `url()`
- `setChildren($children)`
- `children()`
- `numChildren()`
- `hasChildren()`

# Creational Helpers

Most UI elements are very dynamic. The types of object to create is often read from a string in a configuration object. Therefore, factories are the preferred way of instanciating new UI items.

Ui items have also many inter-connected dependencies. Builders should therefore be used for object creation / instanciation. They use a factory internally, and have a `build($opts)` methods that allow to retrieve class name from a dynamic source, do initialization, dpendencies management and more. Builders require `Pimple` for a DI container.

## Factories

- `\Charcoal\Ui\Dashboard\DashboardFactory`
- `\Charcoal\Ui\Layout\LayoutFactory`
- `\Charcoal\Ui\Form\FormFactory`
- `\Charcoal\Ui\FormGroup\FormGroupFactory`
- `\Charcoal\Ui\FormInput\FormInputFactory`
- `\Charcoal\Ui\Menu\MenuFactory`
- `\Charcoal\Ui\MenuItem\MenuItemFactory`

## Builders

- `\Charcoal\Ui\Dashboard\DashboardBuilder`
- `\Charcoal\Ui\Layout\LayoutBuilder`
- `\Charcoal\Ui\Form\FormBuilder`
- `\Charcoal\Ui\FormGroup\FormGroupBuilder`
- `\Charcoal\Ui\FormInput\FormInputBuilder`
- `\Charcoal\Ui\Menu\MenuBuilder`
- `\Charcoal\Ui\MenuItem\MenuItemBuilder`


# Service Providers

Service providers are provided in the `Charcoal\Ui\ServiceProvider` namespace for for convenience. They are the recommended way of using `charcoal-ui`, as they register all the creational utilities to a container, taking care of dependencies.

- `\Charcoal\Ui\ServiceProvider\DashboardServiceProvider`
	+ `dashboard/factory`
	+ `dashboard/builder`
- `\Charcoal\Ui\ServiceProvider\FormServiceProvider`
	+ `form/factory`
	+ `form/builder`
	+ `form/group/factory`
	+ `form/group/builder`
	+ `form/input/factory`
	+ `form/input/builder`
- `\Charcoal\Ui\ServiceProvider\LayoutServiceProvider`
	+ `layout/factory`
	+ `layout/builder`
- `\Charcoal\Ui\ServiceProvider\MenuServiceProvider`
	+ `menu/factory`
	+ `menu/builder`
	+ `menu/item/factory`
	+ `menu/item/builder`
- `\Charcoal\Ui\ServiceProvider\UiServiceProvider`
	+ Register all the other service providers (dashboard, form, layout and menu).

## Required services

There are a few dependencies on external services, that should be set on the same DI container as the one passed to the service providers:

- `logger`, a PSR-3 logger instance.
	+ Typically a `monolog` instance from `charcoal-app`.
- `view`, a `\Charcoal\View\ViewInterface` instance.
	+ Typically provided with `\Charcoal\App\Provider\ViewServiceProvider`.

# Development

## Development dependencies

- `npm`
- `grunt` (install with `npm install grunt-cli`)
- `composer`
- `phpunit`

## Coding Style

The Charcoal-UI module follows the Charcoal coding-style:

- [_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
- [_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
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

### Unreleased


