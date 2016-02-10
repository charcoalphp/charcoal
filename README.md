Charcoal UI
===========

The `Charcoal\Ui` module provides tools to create UI elements (dashboards, layouts, forms and menus) from simple metadata / config.

# Table of contents

- [How to install](#how-to-install)
	- [Dependencies](#dependencies)
- [Dashboard](#dashboard)
- [Layout](#layout)
- [Widget](#widget)
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
	- Older versions of PHP are deprecated, therefore not supported.
- [`psr/log`](http://www.php-fig.org/psr/psr-3/)
	- A PSR-3 compliant logger should be provided to the various services / classes.
- [`locomotivemtl/charcoal-config`](https://github.com/locomotivemtl/charcoal-config)
	- The view objects are _configurable_ with `\Charcoal\View\ViewConfig`.

# Dashboard

Dashboards define a layout of widgets.

## Dashboard config

- `template_ident`
- `template_controller`
- `layout`
- `widgets`

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

# Widget

Widgets are simply viewable objects that can be rendered in a layout.

## Widget config

- `template_ident`
- `template_controller`

## Widget dependencies:

- `logger`
- `view`

# Form

Forms define a layout of form groups, form options, data and medata.

## Form config

- `title`
- `subtitle`
- `action`
- `method`
- `template_ident`
- `template_controller`
- `layout`
- `groups`
- `form_data`
- `form_metadata`
- `description`
- `notes`

## Form dependencies

- `view`
- `group_factory`

## Form API

- `setTitle($title)`
- `title()`
- `setSubtitle($subtitle)`
- `subtitle()`
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
- `title`
- `subtitle`
- `template_ident`
- `template_controller`
- `priority`
- `layout`
- `properties`

# Form Input

- `form`
- `label`
- `property_ident`
- `template_ident`
- `template_controller`
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

Factories and builders

## Factories

- DashboardFactory
- LayoutFactory
- WidgetFactory
- FormFactory
- FormGroupFactory
- FormInputFactory
- MenuFactory
- MenuItemFactory

## Builders

- DashboardBuilder
- LayoutBuilder
- WidgetBuilder
- FormBuilder
- FormGroupBuilder
- FormInputBuilder
- MenuBuilder
- MenuItemBuilder

# Service Providers

- DashboardServiceProvider
	+ `dashboard/factory`
	+ `dashboard/builder`
	+ `layout/factory`
	+ `layout/builder`
	+ `widget/factory`
	+ `widget/builder`
- FormServiceProvider
	+ `form/factory`
	+ `form/builder`
	+ `form/group/factory`
	+ `form/group/builder`
	+ `form/input/factory`
	+ `form/input/builder`
- MenuServiceProvider
	+ `menu/factory`
	+ `menu/builder`
	+ `menu/item/factory`
	+ `menu/item/builder`

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


