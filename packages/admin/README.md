Charcoal Admin
==============

The Admin package provides a customizable administration panel to manage data for Web applications and APIs.

## Installation

```shell
composer require charcoal/admin
```

## Overview

### Core concepts

The _charcoal admin control panel_ is:

* Additional `admin` metadata on charcoal objects and models, which controls automatically how they can be customized in the backend.
* A user / authentication system, which uses ACL for permissions.
* A customizable 2-level menu, which builds custom backend for every install.
* Dashboards and widgets. With some prebuilt functionalities for:
  * Listing collection of objects (`admin/object/collection`), customizable from the object's _admin metadata_.
  * Creating and editing objects (`admin/object/edit`), customizable from the objects's _admin metadata_.
* Set of _scripts_ to manage objects and the backend from the CLI.

### What's inside this module?

Like all Charcoal projects / modules, the main components are:

* **Autoloader**
  * _PSR-4_, Provided by Composer.
* **Config**
  * As JSON or PHP files in the [config/](config/) directory.
* **Front Controller**
  * The admin front controller is handled in the `\Charcoal\Admin\Module` class.
* **Objects**
  * Typically  into `\Charcoal\Object\Content` and `\Charcoal\Object\UserData`
  * Extends `\Charcoal\Model\AbstractModel`, which implements the following interface:
    * `\Charcoal\Model\ModelInterface`
    * `\Charcoal\Core\IndexableInterface`
    * `\Charcoal\Metadata\DescribableInterface`
    * `\Charcoal\Source\StorableInterface`
    * `\Charcoal\Validator\ValidatableInterface`
    * `\Charcaol\View\ViewableInterface`
  * PHP Models in `src/Charcoal/Boilerplate/`
  * JSON metadata in `metadata/charcoal/boilerplate/`
* **Templates**
  * Templates are specialized Model which acts as View / Controller
  * Split in `Templates`, `Widgets`, `PropertyDisplay`, and `PropertyInput`
  * PHP Models in `src/Charcoal/Boilerplate/Template/`
  * Mustache views (templates) in `templates/boilerplate/`
  * Optionnally, templates metadata in `metdata/boilerplate/template/`
* **Actions**
  * Actions handle input and provide a response to a request
  * They create the Admin REST API.
  * The PHP classes in `src/Charcoal/Boilerplate/Action`
* **Assets**
  * Assets are files required to be on the webserver root
  * Scripts, in `src/scripts/` and compiled in `www/assets/scripts/`
  * Styles , with SASS in `src/styles/` and compiled CSS in `www/assets/styles/`
  * Images, in `www/assets/images/`

### Users

Authentication is done through the `Charcoal\Admin\User` class. It reuses the authentication, authorization and user model provided by [charcoal/user].

## UI Elements

User-Interface elements in the Admin package (or any other Charcoal modules, in fact), are composed of:

* A PHP Controller, in _src/Charcoal/Admin/{{type}}/{{ident}}_
* A mustache templates, in _templates/charcoal/admin/{{type}}/{{ident}}_
* Optional additional metadata, in _metadata/charcoal/admin/{{type}}/{{ident}}_

There are 3 main types of UI Elements: _Templates_, _Widgets_ and _Property Inputs_.

### Templates

See the [src/Charcoal/Admin/Templates](src/Charcoal/Admin/Template) directory for the list of available Templates in this module. Note that the template views themselves (the mustache templates) are located in [templates/charcoal/admin/template/](templates/charcoal/admin/template/) directory.

In addition to being standard Template Models (controllers), all _Template_ of the admin module also implements the `\Charcoal\Admin\Template` class.

This class provides additional controls to all templates:

* `has_feedbacks` and `feedbacks`
* `title`, `subtitle`, `show_title` and `show_subtitle`
* `auth_required`
  * Protected, true by default. Set to false for templates that do not require an authenticated admin user.

### Widgets

The following base widgets are available to build the various _admin_ templates:

* `Dashboard`
* `Feedbacks`
* `Form`
* `FormGroup`
* `FormProperty`
* `Graph/Bar`
* `Graph/Line`
* `Graph/Pie`
* `Layout`
* `MapWidget`
* `Table`
* `TableProperty`

### Property Inputs

Similar to other UI elements, _Inputs_ are specialized widgets that are meant to display a "form element" for a `Property`. Properties models are defined in the [charcoal/property] package.

The following property inputs are available  to build forms in the _admin_ module:

* `Audio`
  * A special HTML5 widget to record an audio file from the microphone.
* `Checkbox`
* `DateTimePicker`
  * A date-time picker widget.
  * Requires the ``
* `File`
  * A default `<input type="file">` that can be used as a base for all _File_ properties.
* `Image`
  * A specialized file input meant for uploading / previewing images.
* `MapWidget`
  * A specialized widget to edit a point on a map.
  * Requires google-map.
* `Number`
* `Radio`
* `Readonly`
* `Select`
* `Switch`
  * A specialized _Checkbox_ meant to be displayed as an on/off switch.
* `Text`
  * A default `<input type="text">` that can be used with most property types.
* `Textarea`
  * A default `<textarea>` editor that can be used with most textual property types.
* `Tinymce`
  * A specialized _Textarea_ wysiwyg editor.
  * Requires the `tinymce` javascript library.
* `Selectize`
  * A specialized hybrid between a _Textbox_ and _Select_ jQuery based.
  * Highly customizable.
  * Requires the `selectize` javascript library.

#### Selectize inputs options

<table width="100%">
<tr>
  <th width="120px" align="left">Name</th>
  <th width="30px" align="left">Type</th>
  <th align="left">Description</th>
  <th width="60px" align="left">Default</th>
</tr>
<tr>
  <td valign="top"><strong>choice_obj_map</strong></td>
  <td valign="top"><em>array</em></td>
  <td valign="top">Custom mapping between an object properties or callable and the selectize. It is discouraged to use renderable data. choice_obj_map must be a mapping with existing object properties.
  <table class="table table-bordered table-hover table-condensed">
          <br/>
          <br/>
          <tbody><tr>
          <td valign="top"><strong>value</strong></td>
          <td>Object property or object callable. Defines the actual value to be registered in the database</td>
          </tr>
          <tr>
          <td valign="top"><strong>label</strong></td>
          <td>Object property or object callable. Defines the visible label of the input.</td>
          </tr>
          </tbody>
      </table>
  </td>
  <td valign="top"><pre>{
  &quot;value&quot; : &quot;id&quot;,
  &quot;label&quot;: &quot;name:title:label:id&quot;
}</pre></td>
</tr>
<tr>
  <td valign="top"><strong>form_ident</strong></td>
  <td valign="top"><em>string|array</em></td>
  <td valign="top">Allow to define a specific object form ident when creating or updating an object. You can specify different form idents for create and update by using the &quot;create&quot; and &quot;update&quot; array keys</td>
  <td valign="top"><code>&quot;quick&quot;</code></td>
</tr>
<tr>
  <td valign="top"><strong>selectize_templates</strong></td>
  <td valign="top"><em>string|array</em></td>
  <td valign="top">Allow custom rendering for selectize [item] and [option]. Overrule choice_obj_map[label]. Priotize using this for rendering custom labels instead of choice_obj_map.<br><br>The value can either be a string with render tags, a path to a custom template or even an array mapping to handle "item", "option", "controller" and "data" individually.
  <table class="table table-bordered table-hover table-condensed">
          <br/>
          <br/>
          <tbody><tr>
          <td valign="top"><strong>item</strong><br>(Can be a renderable string or template path)</td>
          <td>Custom renderable html or mustache template for the selectize item. [Item] is the term used to refer to a selected choice.</td>
          </tr>
          <tr>
          <td valign="top"><strong>option</strong><br>(Can be a renderable string or template path)</td>
          <td>Custom renderable html or mustache template for the selectize option. [Option] is the term used to refer to an available choice.</td>
          </tr>
          <tr>
          <td valign="top"><strong>controller</strong></td>
          <td>Defines a rendering context (path to php controller). (optional) Default context is the object itself.</td>
          </tr>
          <tr>
          <td valign="top"><strong>data</strong>(array)</td>
          <td>Provides additional data to the controller</td>
          </tr>
          </tbody>
      </table>
  </td>
  <td valign="top"><code>{}</code></td>
</tr>
<tr>
  <td valign="top"><strong>allow_create</strong></td>
  <td valign="top"><em>bool</em></td>
  <td valign="top">Display a &#39;create&#39; button which triggers the selectize create functionality.</td>
  <td valign="top"><code>false</code></td>
</tr>
<tr>
  <td valign="top"><strong>allow_update</strong></td>
  <td valign="top"><em>bool</em></td>
  <td valign="top">Display an &#39;update&#39; button which triggers the selectize update functionality. Applies to currently selected element.</td>
  <td valign="top"><code>false</code></td>
</tr>
<tr>
  <td valign="top"><strong>allow_clipboard_copy</strong></td>
  <td valign="top"><em>bool</em></td>
  <td valign="top">Display a &#39;copy&#39; button which allows the user to easilly copy all selected elements at once.</td>
  <td valign="top"><code>false</code></td>
</tr>
<tr>
  <td valign="top"><strong>deferred</strong></td>
  <td valign="top"><em>bool</em></td>
  <td valign="top">Allow the select to load the dropdown &quot;options&quot; with an ajax request instead of on load. This can speed up the page load when there is a lot of &quot;options&quot;. </td>
  <td valign="top"><code>false</code></td>
</tr>
<tr>
  <td valign="top"><strong>selectize_options</strong></td>
  <td valign="top"><em>array</em></td>
  <td valign="top">Defines the selectize js options. See the <a href="https://github.com/selectize/selectize.js/blob/master/docs/usage.md">Selectize.js doc</a>. Some usefull ones are :
  <ul>
  <li>&quot;maxItems&quot;</li>
  <li>&quot;maxOptions&quot;</li>
  <li>&quot;create&quot;</li>
  <li>&quot;placeholder&quot;</li>
  <li>&quot;searchField&quot;</li>
  <li>&quot;plugins&quot;</li>
  </ul>
  Also, two home made plugins are available : &quot;btn_remove&quot; and &quot;btn_update&quot; that are custom buttons for selected items that work well with charcoal objects and doesn&#39;t break styling.</td>
  <td valign="top"><pre>{
   persist: true,
   preload: "focus",
   openOnFocus: true, 
   labelField: "label",
   searchField: [
     "value",
     "label"
   ]
}</pre>
  </td>
</tr>
</table>

Usage example:

```json
"categories": {
    "type": "object",
    "input_type": "charcoal/admin/property/input/selectize",
    "multiple": true,
    "deferred": true,
    "obj_type": "cms/object/news-category",
    "pattern": "title",
    "choice_obj_map": {
        "value": "ident",
        "label": "{{customLabelFunction}} - {{someAdditionalInfo }}"
    },
    "selectize_templates": {
        "item": "project/selectize/custom-item-template",
        "option": "project/selectize/custom-option-template",
        "controller": "project/selectize/custom-template"
    },
    "selectize_options": {
        "plugins": {
            "drag_drop": {},
            "btn_remove": {},
            "btn_update": {}
        }
    },
    "form_ident": {
        "create": "quick.create",
        "update": "quick.update"
    }
}
```

Selectize templates examples:

```json
"selectize_templates": {
    "item": "{{customLabelFunction}} - {{someAdditionalInfo }}",
    "option": "{{customLabelFunction}} - {{someAdditionalInfo }}"
},

---

"selectize_templates": "{{customLabelFunction}} - {{someAdditionalInfo }}",

---

"selectize_templates": "project/selectize/custom-template",

---

"selectize_templates": {
   "item": "project/selectize/custom-item-template",
   "option": "project/selectize/custom-option-template",
   "controller": "project/selectize/custom-template",
   "data": {
        "category": "{{selectedCategory}}"
   }
},
```

### Actions

See the [src/Charcoal/Admin/Action/](src/Charcoal/Admin/Action/) directory for the list of availables Actions in this module.

In addition to being standard Action Models (controllers), all _Action_ of the admin module also implements the `\Charcoal\Admin\Action` class.

### Post Actions

* `admin/login`
* `admin/object/delete`
* `admin/object/save`
* `admin/object/update`
* `admin/widget/load`
* `admin/widget/table/inline`
* `admin/widget/table/inlinue-multi`

### CLI Actions

See the [src/Charcoal/Admin/Action/Cli/](src/Charcoal/Admin/Action/Cli/) directory for the list of all available Cli Actions in this module.

_CLI Actions_ are specialized action meant to be run, interactively, from the Command Line Interface. With the Cli Actions in this module, it becomes quick and easy to manage a Charcoal project directly from a Terminal.

* `admin/objects`
  * List the object of a certain `obj-type`.
* `admin/object/create`
  * Create a new object (and save it to storage) of a certain `obj-type` according to its metadata's properties.
* `admin/object/table/alter`
  * Alter the existing database table of `obj-type` according to its metadata's properties.
* `admin/object/table/create`
  * Create the database table for `obj-type` according to its metadata's properties.
* `admin/user/create`

## Resources

* [Contributing](https://github.com/charcoalphp/.github/blob/main/CONTRIBUTING.md)
* [Report issues](https://github.com/charcoalphp/charcoal/issues) and
  [send pull requests](https://github.com/charcoalphp/charcoal/pulls)
  in the [main Charcoal repository](https://github.com/charcoalphp/charcoal)

[charcoal/property]: https://github.com/charcoalphp/property
[charcoal/user]:     https://github.com/charcoalphp/user
