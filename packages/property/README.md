Charcoal Property
=================

The Property package provides abstract tools for defining object metadata.

## Installation

```shell
composer require charcoal/property
```

## Overview

Properties are defined globally for objects (_Charcoal models_) in its `metadata`. They provide properties definition, storage definition and validation definition.

> The `metadata()` method is defined in `\Charcoal\Model\DescribableInterface`.
> The `properties()` method is defined in `\Charcoal\Property\DescribablePropertyInterface`.

### Property options

The basic property interface (API) requires / provides the following members:

| Name           | [†] | Type                | Description
| -------------- | :-: | ------------------- | -----------
| **ident**      |     | _string_            | The property idenfifier (typically, its containing object matching property name).
| **label**      |     | _Translation_       | ...
| **l10n**       |     | _bool_              | If true, then the data should be stored in a l10n-aware structure (be translatable).s
| **hidden**     |     | _bool_              |
| **multiple**   |     | _bool_              | Multiple values can be held and stored, if true.
| **required**   |  ✓  | _bool_              |
| **unique**     |  ✓  | _bool_              |
| **storable**   |     | _bool_              |
| **active**     |     | _bool_              |

Notes:

* <sup>[†]</sup> Indicates options used in validation.

> All those methods can be accessed either via the `setData()` method or with a standard PSR-1/PSR-2 getter / setter. (`foo` would have `foo()` as a getter and `setFoo()` as a setter).

### Data retrieval

* To get a normalized value, use the `parseVal($val)` method.
* To get a string-safe, displaybale value, use `displayVal($val)`.
* To get the storage-ready format, use `storageVal($val)`.

> Custom data retrieval methods can be further defined in each properties. (For example, formatted date or custom color formats).

### Default validation

Validation is provided with a `Validator` object, from [charcoal/core].

* `required`
* `unique`
* `allow_null`

> Validation is being rebuilt in a new charcoal/validator package.

### Source and storage methods

Properties need 4 method to integrate with a SQL source:

* `sqlEncoding()` _string_ The SQL column encoding & collation (ex: `utf8mb4`)
* `sqlExtra()` _string_ Raw SQL string that will be appended to
* `sqlType()` _string_ The SQL column type (ex: `VARCHAR(16)`)
* `sqlPdoType()` _integer_ The PDO column type (ex: `PDO::PARAM_STR`)

> Those methods are _abstract_ and therefore must be implemented in actual properties.

## Types of properties

* [Boolean](#boolean-property)
* [Color](#color-property)
* ~~Date~~
  * [DateTime](#datetime-property)
  * ~~Day~~
  * ~~Month~~
  * ~~Time~~
  * ~~Year~~
* [File](#file-property)
  * [Audio](#file-audio-property)
  * [Image](#image-property)
  * ~~Video~~
* [Lang](#lang-property)
* [Number](#number-property)
  * ~~Float~~
  * ~~Integer~~
* [Object](#object-property)
* [Id](#id-property)
* [IP](#ip-property)
* [String](#string-property)
  * [Html](#html-string-property)
  * [Password](#password-string-property)
  * [Phone](#phone-string-property)
  * [Text](#text-string-propery)
* ~~Structure~~
  * [MapStructure](#map-structure-property)
  * [ModelStructure](#model-structure-property)

Retrieve

### Boolean Property

The boolean property is one of the simplest possible: it simply holds boolean (`true` / `false`) values.

#### Boolean Property options

The boolean property adds the following concepts to the [basic property options](#basic-property-options):

| Name            | [†] | Type                | Description
| --------------- | :-: | ------------------- | -----------
| **false_label** |  -  | _Translation_       | Label, for "true" value.
| **true_label**  |  -  | _Translation_       | Label, for "false" value.


Notes:

* <sup>[†]</sup> Indicates options used in validation.

> ⚠ Boolean properties can not be multiple. (`multiple()` will always return false). Calling `setMultiple(true)` on a `boolean` property will throw an exception.

### Color Property

The color property stores a color. If alpha is not supported, it is stored as an hexadecimal value (ex: `#5590BA`). If alpha is supported, it is stored as a rgba() string value (ex: `rgb(85, 144, 186, 0.5)`).

#### Color Property options

The boolean property adds the following concepts to the [basic property options](#basic-property-options):

| Name               | [†] | Type                | Description
| ------------------ | :-: | ------------------- | -----------
| **support_alpha**  |  ✓  | _boolean_           | ...

Notes:

* <sup>[†]</sup> Indicates options used in validation.


### DateTime Property

The datetime property store a date (and time) value.

#### DateTime Property options

The datetime property adds the following concepts to the [basic property options](#basic-property-options):

| Name            | [†] | Type                | Description
| --------------- | :-: | ------------------- | -----------
| **min**         |  ✓  | _DateTime_          | Minimum date value. If 0, empty or null, then there is no minimal constraint.
| **max**         |  ✓  | _DateTime_          | Maximum date value. If 0, empty or null, then there is no maximal constraint.
| **format**      |     | _string_            | The date format is a string (in PHP's DateTime `format()`) that manages how to format the date value for display. Defaults to \"Y-m-d H:i:s\".

Notes:

* <sup>[†]</sup> Indicates options used in validation.

> ⚠ DateTime properties can not be multiple. (`multiple()` will always return false). Calling `setMultiple(true)` on a `date-time` property will throw an exception.

### ID Property

The ID property holds an ID, typically the main object identifier (unique index key).

The ID value can be generated by many **mode**:

* `auto-increment` is the default mode. It uses the storage engine (_mysql_) auto-increment feature to keep an auto-incrementing integer index.
* `uniqid` creates a 13-char string with PHP's `uniqid()` function.
* `uuid` creates a 36-char string (a _RFC-4122 v4_ Universally-Unique Identifier, `uuidv4`).

#### ID Property options

The ID property adds the following concepts to the [basic property options](#basic-property-options):

| Name            | [†] | Type                | Description
| --------------- | :-: | ------------------- | -----------
| **mode**        |     | _string_            | The ID generation mode. Can be `auto-increment`, `uniqid` or `uuid`. |

> ⚠ Id properties can not be multiple. (`multiple()` will always return false). Calling `setMultiple(true)` on an `id` property will throw an exception.

#### ID Property custom save

Upon `save($val)`, the ID property auto-generates and ID if its mode is `uniqid` or `uuid`.

> Note: The `auto-increment` mode does not do anything on save; it relies on the storage engine / driver to implement auto-incrementation.

### IP Property

The IP property holds an IP address. Only _IPv4_ addresses are supported for now.

There is 2 different storage modes for IP:

* `string` is the default mode. It stores the IP address like `192.168.1.1`.
* `int` stores the IP as a _signed long integer_.

> ⚠ Ip properties can not be multiple. (`multiple()` will always return false). Calling `setMultiple(true)` on an `ip` property will throw an exception.

### File Property

File property holds an uploadable file.

#### Values

Note that only a _relative_<sup>1</sup> file path should be stored in the database.

<sup>1</sup> Relative to project's `ROOT` + the property's `upload_path`.

#### File Property options

The file property adds the following concepts to the [basic property options](#basic-property-options):

| Name                  | [†] | Type        | Description
| --------------------- | :-: | ----------- | -----------
| **public_access**     |     | _bool_      | If the public access is true (default) then the file will be stored in a public filesystem. If not, then it will be stored in a private (non-web-accessible) filesystem.
| **upload_path**       |     | _string_    | The default upload path, relative to `base_url`, where the uploaded files will be stored.
| **overwrite**         |     | _bool_      | If true, when a target file already exists on the filesystem  it will be overwritten. If false, a new unique name will be generated (with a suffix).
| **accepted_mimeypes** |  ✓  | _string[]_  | List of accepted mimetypes. Leave empty to accept all types (no mimetype constraint).
| **max_filesize**      |  ✓  | _integer_   | Maximum allowed file size, in bytes. If 0, null or empty, then there are no size constraint.

Notes:

* <sup>[†]</sup> Indicates options used in validation.

#### Additional file methods

* `mimetype()` and `setMimetype()`
* `filesize()` and `setFilesize()`
* `dataUpload()`
* `fileUpload()`

#### File Property Custom Save

Upon `save($val)`, the File property attempts to upload the file or create a file from data, if necessary. The uploaded file's path (_relative_) is returned.

#### Specialized File properties

* [`AudioProperty`](#audio-file-property)
* [`ImageProperty`](#image-file-property)
* ~~VideoProperty~~

### Audio File Property

Audio property are specialized [file property](#file-property) thay may only contain audio data.

#### Accepted mimetypes

The `AudioProperty` extends `FileProperty` therefore provides "accepted mimetypes".

Default accepted mimetypes are:

* `audio/mp3`
* `audio/mpeg`
* `audio/wav`
* `audio/x-wav`.

#### Audio file Property options

The audio property adds the following concepts to the [file property options](#file-property-options):

| Name            | [†] | Type                | Description
| --------------- | :-: | ------------------- | -----------
| **min_length**  |  ✓  | _DateTime_          | Minimum audio length, in seconds. If 0, null or empty, then there is no minimal constraint.
| **max_length**  |  ✓  | _DateTime_          | Maximum audio length, in seconds. If 0, null or empty, then there is no maximal constraint.

Notes:

* <sup>[†]</sup> Indicates options used in validation.

### Image File Property

Image property are specialized [file property](#file-property) thay may only contain image data.

#### Accepted mimetypes

The `ImageProperty` extends `FileProperty` therefore provides "accepted mimetypes".

Default accepted mimetypes are:

* `image/gif`
* `image/jpg`
* `image/jpeg`
* `image/pjpeg`
* `image/png`
* `image/svg+xml`
* `image/webp`

#### Image file Property options

The audio property adds the following concepts to the [file property options](#file-property-options):

| Name            | [†] | Type                | Description
| --------------- | :-: | ------------------- | -----------
| **effects**     |  ✓  | _array_             | Array of [charcoal/image] effects options.

Notes:

* <sup>[†]</sup> Indicates options used in validation.


### Lang Property

Language properties hold a language value.

> The `LangProperty` implements the `SelectablePropertyInterface`, but hardcode its `choices()` method to return the active language (from [charcoal/translator]).

### Number Property

Number properties hold any kind of numeric data.

### Object Property

Object properties hold a reference to an external object of a certain type.

#### Values

The target's `identifer` (determined by its _obj-type_'s `key`, which is typically "id") is the only thing held in the value / stored in the database. When displayed (with `displayVal()`), a string representation of the object should be rendered.

#### Object Property options

The object property adds the following concepts to the [basic property options](#basic-property-options):

| Name            | [†] | Type                | Description
| --------------- | :-: | ------------------- | -----------
| **obj-type**    |  ✓  | _string_            | The target object's type. In a string format that can be fetched with a `ModelFactory`. |
| **pattern**     |     | _string_            | The rendering pattern, used to display the object(s) when necessary.

Notes:

* <sup>[†]</sup> Indicates options used in validation.

### String Property

#### String Property options

The string property adds the following concepts to the basic property options:

| Name            | [†] | Type                | Description
| --------------- | :-: | ------------------- | -----------
| **max_length**  |  ✓  | _integer_           | Maximum allowed length, in (_multibytes_) characters. |
| **min_length**  |  ✓  | _integer_           | Minimum allowed length, in (_multibytes_) characters. |
| **regexp**      |  ✓  | _string_            | A validation regular expression that must be matched exactly. |
| **allow_empty** |  ✓  | _bool_              | If empty strings are allowed.

Notes:

* <sup>[†]</sup> Indicates options used in validation.

#### String default data

By default, the `string` property is targetted at small string (a maximum length) of `255` characters

#### Specialized String properties

* [`HtmlProperty`](#html-string-property)
* [`PasswordProperty`](#password-string-property)
* [`PhoneProperty`](#phone-string-property)
* [`TextProperty`](#text-string-property)

### Html String Property

HTML properties are specialized [string property](#string-property) that may only contain HTML strings (instead of plain string).

### Password String Property

Password properties are specialized [string property](#string-property) that holds (encrypted) password data.

Encryption is performed with PHP's `password_hash` function.

#### Password Property Custom Save

Upon `save($val)`, the Password property hashes (or rehashes) the password to be stored safely (encrypted).

### Phone String Property

Phone properties are specialized [string property](#string-property) that holds a phone number.

Right now, only "north-american" phone number styles are supported.

### Text String Property

Text properties are specialized [string property](#string-property) thay typically holds longer text strings.

### Map Structure Property

Map structure properties hold complex map structure data, which can be points (markers), lines and / or polygons.

### Properties table summary, for developers

| Name         | Data type | Multiple | Custom Save | Custom Parse |
| ------------ | :-------: | :------: | :---------: | :----------: |
| Audio        | mixed     |          |             |              |
| Boolean      | bool      | **No**   |             |              |
| Color        | string    |          |             | **Yes**      |
| DateTime     | DateTime  | **No**   |             | **Yes**      |
| File         | mixed     |          | **Yes**     |              |
| Html         | string    |          |             |              |
| Id           | mixed     | **No**   | **Yes**     |              |
| Image        | mixed     |          |             |              |
| Ip           | mixed     | **No**   |             |              |
| Lang         | string    |          |             |              |
| MapStructure | mixed     |          |             |              |
| Number       | numeric   |          |             |              |
| Object       | mixed     |          |             | **Yes**      |
| Password     | string    |          | **Yes**     |              |
| Phone        | string    |          |             |              |
| String       | string    |          |             |              |
| Structure    | mixed     |          |             | **Yes**      |
| Text         | string    |          |             |              |
| Url          | string    |          |             |              |

## Resources

* [Contributing](https://github.com/charcoalphp/charcoal/blob/main/CONTRIBUTING.md)
* [Report issues](https://github.com/charcoalphp/charcoal/issues) and
  [send pull requests](https://github.com/charcoalphp/charcoal/pulls)
  in the [main Charcoal repository](https://github.com/charcoalphp/charcoal)

[charcoal/core]:       https://github.com/charcoalphp/core
[charcoal/image]:      https://github.com/charcoalphp/image
[charcoal/translator]: https://github.com/charcoalphp/translator
