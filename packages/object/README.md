Charcoal Object
===============

The Object package provides abstract objects, behaviours, and tools for building user-facing Web sites.

## Installation

```shell
composer require charcoal/object
```

## Overview

The package provides a bunch of basic classes, helpers as well as object behaviors (interfaces + traits).

### Basic classes

All charcoal project object classes should extend one of the 2 base classes, [`\Charcoal\Object\Content`](#content), for data created and managed by administrators or [`\Charcoal\Object\UserData`](#userdata), for data created from clients / users.

#### Content

The **Content** base class should be used for all objects which can be "managed". Typically by an administrator, via the [charcoal/admin] package. It adds the "active" flag to objects as well as creation and modification informations.

**API**

* ` setActive($active)`
* `active()`
* `setPosition($position)`
* `position()`
* `setCreated($created)`
* `created()`
* `setCreatedBy($createdBy)`
* `createdBy()`
* `setLastModified($lastModified)`
* `lastModified()`
* `setLastModifiedBy($lastModifiedBy)`
* `lastModifiedBy()`

> The `Content` class extends `\Charcoal\Model\AbstractModel` from the [charcoal/core] module, which means that it also inherits its API as well as the `DescribableInterface` (`metadata()`, `setMetadata()` and `loadMetadata()`, amongst others) and the `StorableInterface` (`id()`, `key()`, `save()`, `update()`,  `delete()`, `load()`, `loadFrom()`, `loadFromQuery()`, `source()` and `setSource()`, amongst others).
>
> The `AbstractModel` class extends `\Charcoal\Config\AbstractEntity` which also defines basic data-access methods (`setData()`, `data()`, `keys()`, `has()`, `get()`, `set()`, plus the `ArrayAccess`, `JsonSerializable` and `Serializable` interfaces).

**Properties (metadata)**

| Property               | Type        | Default     | Description |
| ---------------------- | ----------- | ----------- | ----------- |
| **active**             | `boolean`   | `true`      | …           |
| **position**           | `number`    | `null`      | …           |
| **created**            | `date-time` | `null` [1]  | …           |
| **created_by**         | `string`    | `''` [1]    | …           |
| **last_modified**      | `date-time` | `null` [2]  | …           |
| **last\_modified\_by** | `string`    | `''` [2]    | …           |

<small>[1] Auto-generated upon `save()`</small><br>
<small>[2] Auto-generated upon `update()`</small><br>

> Default metadata is defined in `metadata/charcoal/object/content.json`

#### UserData

The **UserData** class should be used for all objects that are expected to be entered from the project's "client" or "end user".

**API**

* `setIp($ip)`
* `ip()`
* `setTs($ts)`
* `ts()`
* `setLang($lang)`
* `lang()`

> The `Content` class extends `\Charcoal\Model\AbstractModel` from the [charcoal/core] module, which means that it also inherits its API as well as the `DescribableInterface` (`metadata()`, `setMetadata()` and `loadMetadata()`, amongst others) and the `StorableInterface` (`id()`, `key()`, `save()`, `update()`,  `delete()`, `load()`, `loadFrom()`, `loadFromQuery()`, `source()` and `setSource()`, amongst others).
>
> The `AbstractModel` class extends `\Charcoal\Config\AbstractEntity` which also defines basic data-access methods (`setData()`, `data()`, `keys()`, `has()`, `get()`, `set()`, plus the `ArrayAccess`, `JsonSerializable` and `Serializable` interfaces).

**Properties (metadata)**

| Property  | Type        | Default     | Description |
| --------- | ----------- | ----------- | ----------- |
| **ip**    | `ip`        | `null` [1]  | …           |
| **ts**    | `date-time` | `null` [1]  | …           |
| **lang**  | `lang`      | `null` [1]  | …           |

<small>[1] Auto-generated upon `save()` and `update()`</small><br>

> Default metadata is defined in `metadata/charcoal/object/user-data.json`

### Object behaviors

* [Archivable](#archivable)
* [Categorizable](#categorizable)
* [Category](#category)
* [Hierarchical](#hierarchical)
* [Publishable](#publishable)
* [Revisionable](#revisionable)
* [Routable](#routable)

#### Archivable

_The archivable behavior is not yet documented. It is still under heavy development._

#### Categorizable

**API**

* `setCategory($category)`
* `category()`
* `setCategoryType($type)`
* `categoryType()`

**Properties (metadata)**

| Property        | Type       | Default     | Description |
| --------------- | ---------- | ----------- | ----------- |
| **category**    | `object`   | `null`      | The object's category.[1] |

<small>[1] The category `obj_type` must be explicitely set in implementation's metadata.</small>

> Default metadata is defined in `metadata/charcoal/object/catgorizable-interface.json`

#### Category

**API**

* `setCategoryItemType($type)`
* `categoryItemType()`
* `numCategoryItems()`
* `hasCategoryItems()`
* `categoryItems()`

**Properties (metadata)**

| Property          | Type       | Default     | Description |
| ----------------- | ---------- | ----------- | ----------- |
| **category_item** | `string`   | `null`      | …           |

> Default metadata is defined in `metadata/charcoal/object/catgory-interface.json`

#### Hierarchical

**API**

* `hasMaster()`
* `isTopLevel()`
* `isLastLevel()`
* `hierarchyLevel()`
* `master()`
* `toplevelMaster()`
* `hierarchy()`
* `invertedHierarchy()`
* `isMasterOf($child)`
* `recursiveIsMasterOf($child)`
* `hasChildren()`
* `numChildren()`
* `recursiveNumChildren()`
* `children()`
* `isChildOf($master)`
* `recursiveIsChildOf($master)`
* `hasSiblings()`
* `numSiblings()`
* `siblings()`
* `isSiblingOf($sibling)`

**Properties (metadata)**

| Property      | Type       | Default     | Description |
| ------------- | ---------- | ----------- | ----------- |
| **master**    | `object`   | `null`      | The master object (parent in hierarchy). |

> Default metadata is defined in `metadata/charcoal/object/hierarchical-interface.json`.

#### Publishable

* `setPublishDate($publishDate)`
* `publishDate()`
* `setExpiryDate($expiryDate)`
* `expiryDate()`
* `setPublishStatus($status)`
* `publishStatus()`
* `isPublished()`

**Properties (metadata)**

| Property           | Type         | Default    | Description |
| ------------------ | ------------ | ---------- | ----------- |
| **publishDate**    | `date-time`  | `null`     | …           |
| **expiryDate**     | `date-time`  | `null`     | …           |
| **publishStatus**  | `string` [1] | `'draft'`  | …           |

> Default metadata is defined in `metadata/charcoal/object/publishable-interface.json`.

#### Revisionable

Revisionable objects implement `\Charcoal\Object\Revision\RevisionableInterface`, which can be easily implemented by using `\Charcoal\Object\Revision\RevisionableTrait`.

Revisionable objects create _revisions_ which logs the changes between an object's versions, as _diffs_.

**API**

* `setRevisionEnabled(bool$enabled)`
* `revisionEnabled()`
* `revisionObject()`
* `generateRevision()`
* `latestRevision()`
* `revisionNum(integer $revNum)`
* `allRevisions(callable $callback = null)`
* `revertToRevision(integer $revNum)`

**Properties (metadata)**

_The revisionable behavior does not implement any properties as all logic & data is self-contained in the revisions._

#### Routable

_The routable behavior is not yet documented. It is still under heavy development._

### Helpers

#### ObjectDraft

…

#### ObjectRevision

Upon every `update` in _storage_, a revisionable object creates a new *revision* (a `\Charcoal\Object\ObjectRevision` instance) which holds logs the changes (_diff_) between versions of an object:

**Revision properties**

| Property           | Type         | Default    | Description |
| ------------------ | ------------ | ---------- | ----------- |
| **target_type**    | `string`     | `null`     | The object type of the target object.
| **target_id**      | `string`     | `null`     | The object idenfiier of the target object.
| **rev_num**        | `integer`    | `null`     | Revision number, (auto-generated).
| **ref_ts**         | `date-time`  |            |
| **rev_user**       | `string`     | `null`     |
| **data_prev**      | `structure`  |            |
| **data_obj**       | `structure`  |            |
| **data_diff**      | `structure`  |            |

**Revision methods**

* `createFromObject(RevisionableInterface $obj)`
* `createDiff(array $dataPrev, array $dataObj)`
* `lastObjectRevision(RevisionableInterface $obj)`
* `objectRevisionNum(RevisionableInterface $obj, integer $revNum)`

#### ObjetSchedule

It is possible, (typically from the charcoal admin backend), to create *schedule* (a `\Charcaol\Object\ObjectSchedule` instance) which associate a set of changes to be applied automatically to an object:

**Schedule properties**

| Property           | Type         | Default    | Description |
| ------------------ | ------------ | ---------- | ----------- |
| **target_type**    | `string`     | `null`     | The object type of the target object.
| **target_id**      | `string`     | `null`     | The object idenfiier of the target object.
| **scheduled_date** | `date-time`  | `null`     |
| **data_diff**      | `structure`  | `[]`       |
| **processed**      | `boolean`    | `false`    |
| **processed_date** |              |            |

**Schedule methods (API)**

* `process([callable $callback, callable $successCallback,callable $failureCallback])`

> Scheduled actions should be run with a timely cron job. The [charcoal/admin] module contains a script to run schedules automatically:
>
> ```shell
> ./vendor/bin/charcoal admin/object/process-schedules`
> ```

## Resources

* [Contributing](https://github.com/charcoalphp/.github/blob/main/CONTRIBUTING.md)
* [Report issues](https://github.com/charcoalphp/charcoal/issues) and
  [send pull requests](https://github.com/charcoalphp/charcoal/pulls)
  in the [main Charcoal repository](https://github.com/charcoalphp/charcoal)

[charcoal/admin]: https://github.com/charcoalphp/admin
[charcoal/core]:  https://github.com/charcoalphp/core
