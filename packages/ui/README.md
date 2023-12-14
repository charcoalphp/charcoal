Charcoal UI
===========

The UI package provides abstract tools for creating user interface elements.

## Installation

```shell
composer require charcoal/ui
```

For Charcoal projects, the service provider can be registered from your configuration file:

```json
{
    "service_providers": {
        "charcoal/ui/service-provider/ui": {}
    }
}
```

## Example

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

## API

### Base UI Item

All UI classes implements the same basic class: `\Charcoal\Ui\UiItemInterface`. This interface defines a basic set of properties that are shared across (almost) all ui item types.

#### Base UI Item config

| Key                | Type                   | Default | Description |
| ------------------ | ---------------------- | ------- | ----------- |
| **type**           | `string`               |         |
| **title**          | `string`<sup>[1]</sup> |         |
| **subtitle**       | `string`<sup>[1]</sup> |         |
| **description**    | `string`<sup>[1]</sup> |         |
| **notes**          | `string`<sup>[1]</sup> |         |
| **template_ident** | `string`               | `''`    | The default view template. |

Notes:

* <sup>[1]</sup> Indicates a multilingual string (`TranslationString`).

#### View Integration

The `UiItemInterface` is a _Viewable_ item; that means it also implements the `\Charcoal\View\ViewableInterface`. The `AbstractUiItem` fully implements this interface by using `\Charcoal\View\ViewableTrait`.

_Viewable_ objects can set a _View_ object with `setView($view)` have a `template_ident` (which can be set with `setTemplateIdent($id)`). See the [charcoal/view] module for details.

The easiest way to use a View is by setting a `ViewInterface` object as `view` service on a DI container / Service Provider.

### Dashboard

Dashboards define a _layout_ of _widgets_.

* `layout` is a `LayoutInterface` object that can be created with a `LayoutBuilder`.
* `widgets` is a collection of any `UiItemInterface` objects.
  * Any  PHP class can actually be a "widget", but base widgets are provided as convenience.

#### Dashboard config

| Key         | Type           | Default     | Description |
| ----------- | -------------- | ----------- | ----------- |
| **type**    | `string`       |
| **layout**  | `LayoutConfig` |
| **widgets** | `array`        |


#### Dashboard dependencies

* `logger`
* `view`
* `widget_factory`

#### Dashboard API

* `setLayout()`
* `layout()`
* `setWidgets(array $widgets)`
* `widgets()`
* `addWidget()`
* `numWidgets()`
* `hasWidget()`

### Layout

Layouts define a grid (column-based) structure.

#### Layout config

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

#### Layout API

* `setStructure(array $layouts)`
* `structure()`
* `numRows()`
* `rowIndex($position = null)`
* `rowData($position = null)`
* `rowNumColumns($position = null)`
* `rowNumCells($position = null)`
* `rowFirstCellIndex($position = null)`
* `cellRowIndex($position = null)`
* `numCellsTotal()`
* `cellSpan($position = null)`
* `cellSpanBy12($position = null)`
* `cellStartsRow($position = null)`
* `cellEndsRow($position = null)`
* `start()`
* `end()`

#### Layout Aware objects

The UI package has three basic objects that use a layout: _dashboards_, _forms_ and _form groups_.

Those classes implement the Layout requirement by implementing the `\Charcoal\Ui\Layout\LayoutAwareInterface` with the use of its corresponding `LayoutAwareTrait`.

### Form

Forms define a layout of form groups, form options, data and metadata.

* Forms have [_groups_](#form-group), which have [_inputs_](#form-input).
* Groups can be layouted with a `layout` object.
* Form can be pre-populated with _form data_.
* _Metadata_ ca

#### Form config

| Key           | Type            | Default     | Description |
| ------------- | --------------- | ----------- | ----------- |
| **type**      | `string`        |
| **action**    | `string`        | `''`        | URL where the form will be submitted. |
| **method**    | `string`        | `'post'`    | HTTP method to submit ("post" or "get"). |
| **layout**    | `LayoutConfig`  |
| **groups**    | `FormGroupConfig[]` |
| **form_data** | `array`         |
| **metadata**  | `array`         |


#### Form dependencies

* `view`
* `group_factory`

#### Form API

* `setAction($action)`
* `action()`
* `setMethod($method)`
* `method()`
* `setGroups(array $groups)`
* `groups()`
* `addGroup($groupIdent, $groupData)`
* `numGroups()`
* `hasGroups()`
* `setFormData(array $formData)`
* `formData()`
* `addFormData()`

### Form Group

#### Form group config

| Key                     | Type           | Default     | Description |
| ----------------------- | -------------- | ----------- | ----------- |
| **form**                |
| **template_ident**      | `string`       |
| **template_controller** | `string`       |
| **priority**            | `int`          |
| **layout**              | `LayoutConfig` |
| **properties**          | `array`        |

#### Form group API

* `setForm($form)`
* `setInputs(array $groups)`
* `inputs()`
* `addInput($inputIdent, $inputData)`
* `numInputs()`
* `hasInputs()`

### Form Input

* `form`
* `label`
* `property_ident`
* `template_ident`
* `template_data`
* `read_only`
* `required`
* `disabled`
* `multiple`
* `input_id`
* `input_name`

### Menu

### Menu Item

Menu items define a menu level (ident, label and url) and its children (array of Menu Item).

#### Menu item config

* `ident`
* `icon_ident`
* `label`
* `url`
* `children`

#### Menu item API

* `setIdent($ident)`
* `ident()`
* `setLabel($label)`
* `label()`
* `setUrl($url)`
* `url()`
* `setChildren($children)`
* `children()`
* `numChildren()`
* `hasChildren()`

### Creational Helpers

Most UI elements are very dynamic. The types of object to create is often read from a string in a configuration object. Therefore, factories are the preferred way of instanciating new UI items.

Ui items have also many inter-connected dependencies. Builders should therefore be used for object creation / instanciation. They use a factory internally, and have a `build($opts)` methods that allow to retrieve class name from a dynamic source, do initialization, dpendencies management and more. Builders require `Pimple` for a DI container.

#### Factories

* `\Charcoal\Ui\Dashboard\DashboardFactory`
* `\Charcoal\Ui\Layout\LayoutFactory`
* `\Charcoal\Ui\Form\FormFactory`
* `\Charcoal\Ui\FormGroup\FormGroupFactory`
* `\Charcoal\Ui\FormInput\FormInputFactory`
* `\Charcoal\Ui\Menu\MenuFactory`
* `\Charcoal\Ui\MenuItem\MenuItemFactory`

#### Builders

* `\Charcoal\Ui\Dashboard\DashboardBuilder`
* `\Charcoal\Ui\Layout\LayoutBuilder`
* `\Charcoal\Ui\Form\FormBuilder`
* `\Charcoal\Ui\FormGroup\FormGroupBuilder`
* `\Charcoal\Ui\FormInput\FormInputBuilder`
* `\Charcoal\Ui\Menu\MenuBuilder`
* `\Charcoal\Ui\MenuItem\MenuItemBuilder`

#### Service Providers

Service providers are provided in the `Charcoal\Ui\ServiceProvider` namespace for for convenience. They are the recommended way of using the UI package, as they register all the creational utilities to a container, taking care of dependencies.

* `\Charcoal\Ui\ServiceProvider\DashboardServiceProvider`
  * `dashboard/factory`
  * `dashboard/builder`
* `\Charcoal\Ui\ServiceProvider\FormServiceProvider`
  * `form/factory`
  * `form/builder`
  * `form/group/factory`
  * `form/input/factory`
  * `form/input/builder`
* `\Charcoal\Ui\ServiceProvider\LayoutServiceProvider`
  * `layout/factory`
  * `layout/builder`
* `\Charcoal\Ui\ServiceProvider\MenuServiceProvider`
  * `menu/factory`
  * `menu/builder`
  * `menu/item/factory`
  * `menu/item/builder`
* `\Charcoal\Ui\ServiceProvider\UiServiceProvider`
  * Register all the other service providers (dashboard, form, layout and menu).

## Resources

* [Contributing](https://github.com/charcoalphp/.github/blob/main/CONTRIBUTING.md)
* [Report issues](https://github.com/charcoalphp/charcoal/issues) and
  [send pull requests](https://github.com/charcoalphp/charcoal/pulls)
  in the [main Charcoal repository](https://github.com/charcoalphp/charcoal)

[charcoal/view]: https://github.com/charcoalphp/view
