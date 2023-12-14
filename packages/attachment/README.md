Charcoal Attachment
===================

The Attachment package provides support for working with relationships between models.

## Installation

```shell
composer require charcoal/attachment
```

## Overview

The package also provides a collection of basic attachments: Document, Embed, Image, Gallery, Link Video, amongst others.

### Objects

Objects in the Attachment package extend `Content`, from [charcoal/object], which is an `AbstractModel`, from [charcoal/core].

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

> Read the [charcoal/object] documentation for the other default properties provided by the `Content` object (and `RevisionableInterface`).

> Read the [charcoal/core] documention for the other default properties provided by `AbstractModel` (and `DescribableInterface` and `StorableInterface`). 

#### Type of Attachment objects

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

### Widgets

The packages provides its own admin widgets namespaced as `Charcoal\Admin`.

The attachment widget can be use more than once in a form. In order for it to work properly, you need to define a group ident `group` different for each instanciated widgets.

```json
"attachment": {
    "type": "charcoal/admin/widget/attachment",
    "group": "main"
}
```

In this case, we set the group to "main". If none defined, the default group will be "generic". Without those ident, widgets won't be able to know which attachments are his.

You can than access a perticular "group" attachments calling the object's method "attachments(group_ident)". In this case, `$object->attachments('main')` will return attachments associated with the widgets that has the group set to "main".

## Configuration

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

## Usage

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

* `charcoal/attachment/object/image`
* `charcoal/attachment/object/gallery`
* `charcoal/attachment/object/file`
* `charcoal/attachment/object/link`
* `charcoal/attachment/object/text`
* `charcoal/attachment/object/video`

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

### Attachment creation

The one thing you need to know about the attachment is that it is all in a single table. You can't associate custom objects with other objects if they are not `attachments`.

Then, how could you create new attachments? It all depends on what you want.

#### Adding or modifying properties

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

## Resources

* [Contributing](https://github.com/charcoalphp/.github/blob/main/CONTRIBUTING.md)
* [Report issues](https://github.com/charcoalphp/charcoal/issues) and
  [send pull requests](https://github.com/charcoalphp/charcoal/pulls)
  in the [main Charcoal repository](https://github.com/charcoalphp/charcoal)

[charcoal/core]:   https://github.com/charcoalphp/core
[charcoal/object]: https://github.com/charcoalphp/object
