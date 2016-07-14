Charcoal Attachments
====================

Adds support for working with relationships between models.

## How to install

The preferred (and only supported) way of installing _charcoal-attachment is with **composer**:

```shell
★ composer require beneroch/charcoal-attachment
```


## Dependencies

- [`PHP 5.5+`](http://php.net)
- [`locomotivemtl/charcoal-core`](https://github.com/locomotivemtl/charcoal-core)
- [`locomotivemtl/charcoal-base`](https://github.com/locomotivemtl/charcoal-base)
- [`locomotivemtl/charcoal-admin`](https://github.com/locomotivemtl/charcoal-admin)
- [`locomotivemtl/charcoal-ui`](https://github.com/locomotivemtl/charcoal-ui)
- [`locomotivemtl/charcoal-translation`](https://github.com/locomotivemtl/charcoal-translation)

## Objects

Objects in the `charcoal-attachments` module extends AbstractModel (charcoal-base).

- ***Attachment objects***
	- AbstractAttachment - Attachment Base
	- File
	- Link
	- Image
	- Gallery (image)
	- Text
	- Video

## Widgets

The module provides his own admin widgets namespaced as Charcoal\Admin.

## BUT HOW

The setup is fairly easy, but you need to remember a few things in order for it to work.

### Configurations

Add the views path and metadata path to the config file.
```json
"metadata":{
    "paths":[
        "...",
        "vendor/beneroch/charcoal-attachment/metadata/"
    ]
},
"view": {
    "paths": [
        "...",
        "vendor/beneroch/charcoal-attachment/templates/"
    ]
}
```

Then, we need to add the necessary routes for the widgets in admin.json config file.
```json
"routes":{
    "actions":{
        "join":{
            "ident":"charcoal/admin/action/join",
            "methods":[ "POST" ]
        },
        "add-join":{
            "ident":"charcoal/admin/action/add-join",
            "methods":[ "POST" ]
        },
        "remove-join":{
            "ident":"charcoal/admin/action/remove-join",
            "methods":[ "POST" ]
        }
    }
}
```



### Usage

You need to make your object(s) "Attachment Aware", so that it knows it can have attachments. To do that, use/implement attachmentAware:
```php
use \Charcoal\Attachment\Traits\AttachmentAwareTrait;
use \Charcoal\Attachment\Interfaces\AttachmentAwareInterface;
```

Then, just add in the widget in the edit dashboard or the form like this:
```json
"attachment": {
    "title":{
        "fr":"Documents",
        "en":"Documents"
    },
    "type": "charcoal/admin/widget/attachment",
    "group":"main",
    "attachable_objects":{
        "charcoal/attachment/object/file":{
            "label":{
                "fr":"Document / Fichier",
                "en":"Document / File"
            }
        }
    }
}
```

Available attachable objects as provided by the current modile are:

- `charcoal/attachment/object/image`
- `charcoal/attachment/object/gallery`
- `charcoal/attachment/object/file`
- `charcoal/attachment/object/link`
- `charcoal/attachment/object/text`
- `charcoal/attachment/object/video`

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
    "group":"main"
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
 **/
namespace My\Namespace;
use Charcoal\Attachment\Object\Text as AttachmentText;

class Text extends AttachmentText
{
}
```
Now that we have the extend, let's add to the JSON by creating a `my/namespace/text.json` file.
```JSON
{
    "properties":{
        "description":{
            "editor_options":{
                "style_formats":[],
                "body_class":"s-wysiwyg",
                "content_css":"../../../../../styles/main.css"
            }
        }
    },
    "data":{
        "type":"my/namespace/text"
    }
```
In that case, the editor options are changed to remove the base style formats, change the body class and add the appropriate css. The important part is to set the data type to the current object. This is used in live edit and delete features.

If you added some extra properties, you can use the alter script to add them into the table.

`vendor/bin/charcoal admin/object/table/alter --obj-type=my/namespace/text`


## Notes

**Don't use "attachments" method directly in mustache template**. This will return ALL attachments without considering the group.

Custom templates for the attachment preview in the backend widget is on the to-do list.

Other actions such quick view are on the to-do list as well.
