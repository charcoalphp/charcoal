Charcoal CMS
============

The CMS package provides common objects and tools for building user-facing Web sites.

## Installation

```shell
composer require charcoal/cms
```

For Charcoal projects, the service provider can be registered from your configuration file:

```json
{
    "service_providers": {
        "charcoal/cms/service-provider/cms": {}
    }
}
```

## Usage

### Objects

* [Section](#section-object)
* [Tag](#tag-object)
* [Event](#event-object)
* [FAQ](#faq-object)
* [News](#news-object)

### Section object

A **section**, in Charcoal, is a reachable _page_ on the website, as part of the full hierarchical site map. They can be displayed in menus or breadcrumbs and be reached with a unique URL (`routable`).

Types of sections:

* `blocks`
  * Blocks sections define their content as a structured map of blocks.
* `content`
  * Content sections define their content in a single, simple _HTML_ property.
* `empty`
  * Empty sections are linked to a template but do not require any custom content.
* `external`
  * External sections are simply a redirect to an external (or internal) URL.

All section types, except _external_, make use of a `Template` object to be rendered. Typically, a charcoal `view` make sure of linking the `template` (by default, _mustache_

> Sections are standard Charcoal `Model`, meaning they are _describable_ with a `Metadata` object (which define a map of `properties`) and _storable_ with a `Source` object.

Base section properties:

| Interface     | Name                          | L10n | Type         | Description |
| ------------- | ----------------------------- | :--: | ------------ | ----------- |
| Section       | **section_type**              |      | choice       |
| Section       | **title**                     | ✔    | string       |
| Section       | **subtitle**                  | ✔    | html         |
| Section       | **summary**                   | ✔    | html         |
| Section       | **image**                     | ✔    | image        |
| Section       | **template_ident**            |      | string       |
| Section       | **template_options**          |      | structure    |
| Section       | **content**                   | ✔    | html         |
| Section       | **attachments**               | ✔    | multi-object |
| Section       | **external_url**              | ✔    | url          | For external URLs. Note that all content-related properties are ignored if this property is set.
| Content       | **id**                        |      | id           | The model's `key`.
| Content       | **active**                    |      | bool         | Inactive events should not appear in public API / frontend.
| Content       | **position**                  |      | int          | Default order property.
| Authorable    | **created_by**                |      | string       | Admin user.
| Authorable    | **last\_modified_by**         |      | string       | Admin user.
| Authorable    | **required\_acl_permissions** |      | To do...     |
| Timestampable | **created**                   |      | date-time    |
| Timestampable | **last_modified**             |      | date-time    |
| Hierarchical  | **master**                    |      | object       | `SectionInterface`.
| Routable      | **slug**                      | ✔    | string       | Permalink. Auto-generated from title.

#### Interfaces

From model:

* `Describable`: The objects can be defined by Metadata.
* `Storable`: Objects have unique IDs and can be stored in storage / database.

From content:

* `Content`: A "managed" charcoal model (describable with metadata / storable).
* `Authorable`: Creation and modification user (admin) are kept in the storage.
* `Revisionable`: Copy of changes will be kept upon each object update in the storage.
* `Timestampable`: Creation and modification time are kept into the storage.

From [charcoal/object]:

* `Hierarchicale`: The objects can be stacked hierarchically.
* ~~`Publishable`: Objects have publish status, date and expiry. Allows moderation.~~
* `Routable`: Objects are reachable through a URL.

From [charcoal/cms]:

* `Metatag`: The objects have meta-information for SEO purpose.
* `Searchable`: Extra keywords can be used to help search engine.
* `Templateable`: The objects can be rendered with a template / controller / config combo.

#### Extending the section object

The `\Charcoal\Cms\Section\*` objects are `final`. To extend, use the `\Charcoal\Cms\AbstractSection` base object instead, to make sure no metadata conflicts arise.

### Tag object

**Tag** objects link any objects together by providing an extra taxonomy layer. Tags may also be used to enhance internal search engines.

### Event object

Charcoal **Event** is a specialized content object to describe an event, which typically happens at a given date in a certain location.

Base events properties:

| Interface     | Name                          | L10n | Type      | Description |
| ------------- | ----------------------------- | :--: | --------- | ----------- |
| Event         | **title**                     | ✔    | string    |
| Event         | **subtitle**                  | ✔    | html      |
| Event         | **summary**                   | ✔    | html      |
| Event         | **content**                   | ✔    | html      |
| Event         | **image**                     | ✔    | image     |
| Event         | **start_date**                |      | date-time |
| Event         | **end_date**                  |      | date-time |
| Event         | **info_url**                  | ✔    | image     |
| Content       | **id**                        |      | id        | The model's `key`.
| Content       | **active**                    |      | bool      | Inactive events should not appear in public API / frontend.
| Content       | **position**                  |      | int       | Default order property.
| Authorable    | **created_by**                |      | string    | Admin user.
| Authorable    | **last\_modified_by**         |      | string    | Admin user.
| Authorable    | **required\_acl_permissions** |      | To do...
| Timestampable | **created**                   |      | date-time |
| Timestampable | **last_modified**             |      | date-time |
| Categorizable | **category**                  | ✔    | object    | `EventCategory`, or custom.
| Publishable   | **publishDate**               |      | date-time |
| Publishable   | **expiryDate**                |      | date-time |
| Publishable   | **publishStatus**             |      | string    | `draft`, `pending`, or `published`.
| Routable      | **slug**                      | ✔    | string    | Permalink. Auto-generated from title.
| Metatag       | **meta_title**                | ✔    | string    |
| Metatag       | **meta_description**          | ✔    | string    |
| Metatag       | **meta_image**                | ✔    | image     |
| Metatag       | **meta_author**               | ✔    | string    |
| Templateable  | **controller_ident**          |      | string    |
| Templateable  | **template_ident**            |      | string    |
| Templateable  | **template_options**          |      | structure |

#### Interfaces

From model:

* `Describable`: The objects can be defined by Metadata.
* `Storable`: Objects have unique IDs and can be stored in storage / database.

From content:

* `Content`: A "managed" charcoal model (describable with metadata / storable).
* `Authorable`: Creation and modification user (admin) are kept in the storage.
* `Revisionable`: Copy of changes will be kept upon each object update in the storage.
* `Timestampable`: Creation and modification time are kept into the storage.

From [charcoal/object]:

* `Categorizable`: The objects can be put into a category.
* `Publishable`: Objects have publish status, date and expiry. Allows moderation.
* `Routable`: Objects are reachable through a URL.

From [charcoal/cms]:

* `Metatag`: The objects have meta-information for SEO purpose.
* `Searchable`: Extra keywords can be used to help search engine.
* `Templateable`: The objects can be rendered with a template / controller / config combo.

#### Extending the event object

The `\Charcoal\Cms\Event` object is `final`. To extend, use the `\Charcoal\Cms\AbstractEvent` base object instead, to make sure no metadata conflicts arise.

#### Event categories

**Event category** objects are simple `charcoal/object/category` used to group / categorize events. The default type is `Charcoal\Cms\EventCategory`.

_Events_ implement the `Categorizable` interface, from [charcoal/object].

### FAQ object

**FAQ** objects are a special content type that is split in a "question" / "answer" format.

#### FAQ categories

**FAQ category** objects are simple `charcoal/object/category` used to group / categorize FAQ objects. The default type is `Charcoal\Cms\FaqCategory`.

_FAQs_ implement the `Categorizable` interface, from [charcoal/object].

### News object

News object are a special content type that with a specific news date.

Base news properties:

| Interface     | Name                          | L10n | Type      | Description |
| ------------- | ----------------------------- | :--: | --------- | ----------- |
| News          | **title**                     | ✔    | string    |
| News          | **subtitle**                  | ✔    | html      |
| News          | **summary**                   | ✔    | html      |
| News          | **content**                   | ✔    | html      |
| News          | **image**                     | ✔    | image     |
| News          | **news_date**                 |      | date-time |
| News          | **info_url**                  | ✔    | image     |
| Content       | **id**                        |      | id        | The model's `key`.
| Content       | **active**                    |      | bool      | Inactive news should not appear in public API / frontend.
| Content       | **position**                  |      | int       | Default order property.
| Authorable    | **created_by**                |      | string    | Admin user.
| Authorable    | **last\_modified_by**         |      | string    | Admin user.
| Authorable    | **required\_acl_permissions** |      | To do...
| Timestampable | **created**                   |      | date-time |
| Timestampable | **last_modified**             |      | date-time |
| Categorizable | **category**                  | ✔    | object    | `NewsCategory`, or custom.
| Publishable   | **publishDate**               |      | date-time |
| Publishable   | **expiryDate**                |      | date-time |
| Publishable   | **publishStatus**             |      | string    | `draft`, `pending`, or `published`.
| Routable      | **slug**                      | ✔    | string    | Permalink. Auto-generated from title.
| Metatag       | **meta_title**                | ✔    | string    |
| Metatag       | **meta_description**          | ✔    | string    |
| Metatag       | **meta_image**                | ✔    | image     |
| Metatag       | **meta_author**               | ✔    | string    |
| Templateable  | **controller_ident**          |      | string    |
| Templateable  | **template_ident**            |      | string    |
| Templateable  | **template_options**          |      | structure |

#### Interfaces

From model:

* `Describable`: The objects can be defined by Metadata.
* `Storable`: Objects have unique IDs and can be stored in storage / database.

From content:

* `Content`: A "managed" charcoal model (describable with metadata / storable).
* `Authorable`: Creation and modification user (admin) are kept in the storage.
* `Revisionable`: Copy of changes will be kept upon each object update in the storage.
* `Timestampable`: Creation and modification time are kept into the storage.

From [charcoal/object]:

* `Categorizable`: The objects can be put into a category.
* `Publishable`: Objects have publish status, date and expiry. Allows moderation.
* `Routable`: Objects are reachable through a URL.

From [charcoal/cms]:

* `Metatag`: The objects have meta-information for SEO purpose.
* `Searchable`: Extra keywords can be used to help search engine.
* `Templateable`: The objects can be rendered with a template / controller / config combo.

#### Extending the news object

The `\Charcoal\Cms\News` object is `final`. To extend, use the `\Charcoal\Cms\AbstractNews` base object instead, to make sure no metadata conflicts arise.

#### News categories

**News category** objects are simple `charcoal/object/category` used to group / categorize events. The default type is `Charcoal\Cms\NewsCategory`.

_News_ implement the `Categorizable` interface, from [charcoal/object].

## Resources

* [Contributing](https://github.com/charcoalphp/.github/blob/main/CONTRIBUTING.md)
* [Report issues](https://github.com/charcoalphp/charcoal/issues) and
  [send pull requests](https://github.com/charcoalphp/charcoal/pulls)
  in the [main Charcoal repository](https://github.com/charcoalphp/charcoal)

[charcoal/cms]:    https://github.com/charcoalphp/cms
[charcoal/object]: https://github.com/charcoalphp/object
