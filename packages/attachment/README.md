Charcoal Attachment
===================

Attachments add support for working with relationships between models. Also provided are a usable set of basic attachments:
Document, Embed, Image, Gallery, Link Video, amongst others.

## How to install

The preferred (and only supported) way of installing _charcoal-attachment is with **composer**:

```shell
★ composer require charcoal/attachment
```

## Dependencies

-   [PHP 7.1+](http://php.net)
-   [`charcoal/core`](https://github.com/charcoalphp/core)
-   [`charcoal/base`](https://github.com/charcoalphp/base)
-   [`charcoal/admin`](https://github.com/charcoalphp/admin)
-   [`charcoal/ui`](https://github.com/charcoalphp/ui)
-   [`charcoal/translation`](https://github.com/charcoalphp/translation)

## Objects

Objects in the `charcoal-attachments` module extends `Content`, from `charcoal-object`, which is an `AbstractModel`, from `charcoal-core`.

In addition from the default metadata provided by `Content`, the following properties are default for all `Attachment` objects:

**Standard properties** (used by all attachments objects):

| Property        | Type      | Description |
| --------------- | --------- | ----------- |
| **id**          | `id`      | A uniqid, for referencing. |
| **title**       | `string` (l10n) | 
| **show_title**  | `boolean` |
| **categorie**   | `object (multiple) | By default, categories are `charcoal/attachment/object/category/generic` objects.
| **subtitle**    | `string` (l10n) |
| **description** | `text` (l10n) |
| **keywords**    | `string` (l10n, multiple) | Typically used for searching purpose. |
| **type**        | `string`  |

**Specialized properties** which can be used differently, depending on context:

| Property        | Type      | Description |
| --------------- | --------- | ----------- |
| **link**        | `string` (l10n) | URL. |
| **file**        | `file` (l10n)   | Uploadable file, or "document". |
| **file_size**   | `string`  | The uploaded file size, in bytes (auto-generated). |
| **file_type**   | `string`  | The uploaded file mimetype (auto-generated).
| **thumbnail**   | `image` (l10n)  | 
| **embed**       | `text` (l10n)   | Typically a video. |

All attachments are assumed to have a `title`, `subtitle`, `description` and `keywords`. Some attachments also 

> Read the [`charcoal-object`](https://github.com/charcoalphp/object) documentation for the other default properties provided by the `Content` object (and `RevisionableInterface`).

> Read the [`charcoal-core`](https://github.com/charcoalphp/core) documention for the other default properties provided by `AbstractModel` (and `DescribableInterface` and `StorableInterface`). 


### Type of Attachment objects

-   **Accordion**
    - A `Container` (grouping) attachment, used for accordion type of display.
    - By default, support `text`, `image`, `gallery` and `embed` attachments.
-   **Attachment**
    - The most generic attachment, can be anything.
-   **Container**
    - Base "grouping" attachment.
-   **Embed**
    - Embedded content, typically video embed code.
    - Force the `file` property to be an `image`, and `description` to be `html`.
-   **File**
    - An uploadable _Document_.
-   **Link**
    - A URL (link to a resource).
-   **Image**
    - An uploadable image
    - Force the `file` property to be an `image`.
-   **Gallery**
    - A `Container` (grouping) attachment, used for a gallery of multiple images.
    - Is limited to `image` attachments.
-   **Text**
    - Text (HTML) content.
-   **Video**

## Widgets

The module provides his own admin widgets namespaced as Charcoal\Admin.

## BUT HOW

The setup is fairly easy, but you need to remember a few things in order for it to work.

### Configurations

Add the views path and metadata path to the config file.
```json
"metadata": {
    "paths": [
        "...",
        "vendor/charcoal/attachment/metadata/"
    ]
},
"view": {
    "paths": [
        "...",
        "vendor/charcoal/attachment/templates/"
    ]
},
"translations": {
    "paths": [
        "...",
        "vendor/charcoal/attachment/translations/"
    ]
}
```

Then, we need to add the necessary routes for the widgets in admin.json config file.
```json
"routes": {
    "actions": {
        "join": {
            "ident": "charcoal/admin/action/join",
            "methods": [ "POST" ]
        },
        "add-join": {
            "ident": "charcoal/admin/action/add-join",
            "methods": [ "POST" ]
        },
        "remove-join": {
            "ident": "charcoal/admin/action/remove-join",
            "methods": [ "POST" ]
        }
    }
}
```

### Usage

You need to make your object(s) "Attachment Aware", so that it knows it can have attachments. To do that, use/implement attachmentAware:
```php
use Charcoal\Attachment\Traits\AttachmentAwareTrait;
use Charcoal\Attachment\Interfaces\AttachmentAwareInterface;
```

Then, just add in the widget in the edit dashboard or the form like this:
```json
"attachment": {
    "title": "Documents",
    "type": "charcoal/admin/widget/attachment",
    "group": "main",
    "attachable_objects": {
        "charcoal/attachment/object/file": {
            "label": "Document / File"
        }
    }
}
```

Available attachable objects as provided by the current modile are:

-   `charcoal/attachment/object/image`
-   `charcoal/attachment/object/gallery`
-   `charcoal/attachment/object/file`
-   `charcoal/attachment/object/link`
-   `charcoal/attachment/object/text`
-   `charcoal/attachment/object/video`

To create a new attachment, you need to extend the base Attachment object `charcoal/attachment/object/attachment` and provide a "quick" form.

To remove unnecessary join when deleting an object, you need to add this to your object:

```php
public function preDelete()
{
    // AttachmentAwareTrait
    $this->removeJoins();
    return parent::preDelete();
}
```

## Documentation

Attachment widget can be use more than once in a form. In order for it to work properly, you need to define a group ident `group` different for each instanciated widgets.

```json
"attachment": {
    "type": "charcoal/admin/widget/attachment",
    "group": "main"
}
```

In this case, we set the group to "main". If none defined, the default group will be "generic". Without those ident, widgets won't be able to know which attachments are his.

You can than access a perticular "group" attachments calling the object's method "attachments(group_ident)". In this case, `$object->attachments('main')` will return attachments associated with the widgets that has the group set to "main".

## Attachment creation

The one thing you need to know about the attachment is that it is all in a single table. You can't associate custom objects with other objects if they are not `attachments`.

Then, how could you create new attachments? It all depends on what you want.

### Adding or modifying properties

IF you need to add properties to an existing attachment, you can always extend it. Let's say you want to change the editor options for the description field given with the attachments. The first step is to create a new object that will extend the existing one.

```php
/**
 * Extended text class.
 */
namespace My\Namespace;

use Charcoal\Attachment\Object\Text as AttachmentText;

class Text extends AttachmentText
{
}
```

Now that we have the extend, let's add to the JSON by creating a `my/namespace/text.json` file.

```JSON
{
    "properties": {
        "description": {
            "editor_options": {
                "style_formats": [],
                "body_class": "s-wysiwyg",
                "content_css": "../../../../../styles/main.css"
            }
        }
    },
    "data": {
        "type": "my/namespace/text"
    }
}
```

In that case, the editor options are changed to remove the base style formats, change the body class and add the appropriate css. The important part is to set the data type to the current object. This is used in live edit and delete features.

If you added some extra properties, you can use the alter script to add them into the table.

`vendor/bin/charcoal admin/object/table/alter --obj-type=my/namespace/text`

## Notes

**Don't use "attachments" method directly in mustache template**. This will return ALL attachments without considering the group.

Custom templates for the attachment preview in the backend widget is on the to-do list.

Other actions such quick view are on the to-do list as well.

For a complete project example using `charcoal-attachment`, see the [charcoal-project-boilerplate](https://github.com/locomotivemtl/charcoal-project-boilerplate).


## Development

To install the development environment:

```shell
★ composer install --prefer-source
```

Run the code checkers and unit tests with:

```shell
★ composer test
```

### API documentation

-   The auto-generated `phpDocumentor` API documentation is available at [https://locomotivemtl.github.io/charcoal-attachment/docs/master/](https://locomotivemtl.github.io/charcoal-attachment/docs/master/)
-   The auto-generated `apigen` API documentation is available at [https://locomotivemtl.github.io/charcoal-attachment/apigen/master/](https://locomotivemtl.github.io/charcoal-attachment/apigen/master/)

### Development dependencies

-   `phpunit/phpunit`
-   `squizlabs/php_codesniffer`
-   `php-coveralls/php-coveralls`

### Continuous Integration

| Service | Badge | Description |
| ------- | ----- | ----------- |
| [Travis](https://travis-ci.org/locomotivemtl/charcoal-attachment) | [![Build Status](https://travis-ci.org/locomotivemtl/charcoal-attachment.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-attachment) | Runs code sniff check and unit tests. Auto-generates API documentaation. |
| [Scrutinizer](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-attachment/) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-attachment/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-attachment/?branch=master) | Code quality checker. Also validates API documentation quality. |
| [Coveralls](https://coveralls.io/github/locomotivemtl/charcoal-attachment) | [![Coverage Status](https://coveralls.io/repos/github/locomotivemtl/charcoal-attachment/badge.svg?branch=master)](https://coveralls.io/github/locomotivemtl/charcoal-attachment?branch=master) | Unit Tests code coverage. |
| [Sensiolabs](https://insight.sensiolabs.com/projects/09876d95-da9d-4c23-896f-904be3368c99) | [![SensioLabsInsight](https://insight.sensiolabs.com/projects/09876d95-da9d-4c23-896f-904be3368c99/mini.png)](https://insight.sensiolabs.com/projects/09876d95-da9d-4c23-896f-904be3368c99) | Another code quality checker, focused on PHP. |

### Coding Style

The Charcoal-Attachment module follows the Charcoal coding-style:

-   [_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
-   [_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
-   [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), autoloading is therefore provided by _Composer_.
-   [_phpDocumentor_](http://phpdoc.org/) comments.
-   Read the [phpcs.xml.dist](phpcs.xml.dist) file for all the details on code style.

> Coding style validation / enforcement can be performed with `composer phpcs`. An auto-fixer is also available with `composer phpcbf`.

## Authors

-   Mathieu Ducharme <mat@locomotive.ca>
-   Chauncey McAskill <chauncey@locomotive.ca>
-   Benjamin Roch <benjamin@locomotive.ca>

## License

Charcoal is licensed under the MIT license. See [LICENSE](LICENSE) for details.

## Report Issues

In case you are experiencing a bug or want to request a new feature head over to the [Charcoal monorepo issue tracker](https://github.com/charcoalphp/charcoal/issues)

## Contribute

The sources of this package are contained in the Charcoal monorepo. We welcome contributions for this package on [charcoalphp/charcoal](https://github.com/charcoalphp/charcoal).
