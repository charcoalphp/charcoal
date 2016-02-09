Charcoal Property
=================

Properties define object's metadata / definition.

# How to install

The preferred (and only suppported) way of installing _charcoal-property_ is with **composer**:

```shell
$ composer require locomotivemtl/charcoal-property
```

## Dependencies
- [`PHP 5.5+`](http:///php.net)
- [`psr/log`](http://www.php-fig.org/psr/psr-3/)
	- A PSR-3 compliant logger should be provided to the various services / classes.
- [`locomotivemtl/charcoal-config`](https://github.com/locomotivemtl/charcoal-config)
	- Properties configuration and metadata.
- [`locomotivemtl/charcoal-core`](https://github.com/locomotivemtl/charcoal-core)
	- Required for l10n / translation features.
	- Also required for validator, model and more.
- [`locomotivemtl/charcoal-factory`](https://github.com/locomotivemtl/charcoal-fatory)
	- Dynamic object creation is provided with charcoal factories.
- [`locomotivemtl/charcoal-view`](https://github.com/locomotivemtl/charcoal-view)

# Property options

The basic property interface (API) requires / provides the following members:

| Name           | (V) | Type                | Description
| -------------- | :-: | ------------------- | -----------
| **val**        |     | _mixed_             |  The actual property's data value.
| **label**      |     | _TranslationString_ | ...
| **l10n**       |     | _bool_              | If true, then the data should be stored in a l10n-aware structure (be translatable).s
| **hidden**     |     | _bool_              |
| **multiple**   |     | _bool_              | Multiple values can be held and stored, if true.
| **required**   |  ✓  | _bool_              |
| **unique**     |  ✓  | _bool_              |
| **allow_null** |  ✓  | _bool_              |
| **storable**   |     | _bool_              |
| **active**     |     | _bool_              |
<small>(V) indicates options used in validation</small>

> All those methods can be accessed either via the `setData()` method or with a standard PSR-1/PSR-2 getter / setter. (`foo` would have `foo()` as a getter and `setFoo()` as a setter).

### Data retrieval

The _raw_ data held in a property can be accessed with `setVal()` and `val()`.
To get a string-safe, displaybale value, use `displayVal()`. To get the storage-ready format, use `storageSal()`.

> ⚠ Even for string properties `val()` is not sure to return string values (a property can be multiple, or l10n, for example) so use `displayVal()` when working with displayable stirng is crucial.

The `val()` value will be used for serialization and json serialization.

## Default validation

Validation is provided with a `Validator` object, from charcoal-core.

- `required`
- `unique`
- `allow_null`

## Source and storage methods

Properties need 3 method to integrate with a SQL source:

- `sql_extra()` _string_ Raw SQL string that will be appended to
- `sql_type()` _string_ The SQL column type (ex: `VARCHAR(16)`)
- `sql_pdo_type()` _integer_ The PDO column type (ex: `PDO::PARAM_STR`)

>  Those methods are _abstract_ and therefore must be implemented in actual properties.

# Types of properties

- [Boolean](#boolean-property)
- [Color](#color-property)
- ~~Date~~
	- [DateTime](#datetime-property)
	- ~~Day~~
	- ~~Month~~
	- ~~Time~~
	- ~~Year~~
- [File](#file-property)
	- [Audio](#file-audio-property)
	- [Image](#image-property)
	- ~~Video~~
- [Lang](#lang-property)
- [Number](#number-property)
	- ~~Float~~
	- ~~Integer~~
- [Object](#object-property)
- Id
- [String](#string-property)
	- [Html](#html-string-property)
	- [Password](#password-string-property)
	- [Phone](#phone-string-property)
	- [Text](#text-string-propery)
- ~~Structure~~
	- [MapStructure](#map-structure-property)

Retrieve

## Boolean Property

The boolean property is one of the simplest possible: it simply holds boolean (`true` / `false`) values.

### Boolean Property options

The boolean property adds the following concepts to the [basic property options](#basic-property-options):

| Name            | (V) | Type                | Description
| --------------- | :-: | ------------------- | -----------
| **true_label**  |  ✓  | _DateTime_          | ...
| **max**         |  ✓  | _DateTime_          | ...
| **format**      |     | _string_            |
<small>(V) indicates options used in validation</small>

> ⚠ Boolean properties can not be multiple. (`multiple()` will always return false). Calling `set_multiple(true)` will throw an exception

## Color Property

The color property stores a color. If alpha is not supported, it is stored as an hexadecimal value (ex: `#5590BA`). If alpha is supported, it is stored as a rgba() string value (ex: `rgb(85, 144, 186, 0.5)`).

### Color Property options

The boolean property adds the following concepts to the [basic property options](#basic-property-options):

| Name               | (V) | Type                | Description
| ------------------ | :-: | ------------------- | -----------
| **support_alpha**  |  ✓  | _boolean_           | ...
<small>(V) indicates options used in validation</small>


## DateTime Property

The datetime property store a date (and time) value.

### DateTime Property options

The datetime property adds the following concepts to the [basic property options](#basic-property-options):

| Name            | (V) | Type                | Description
| --------------- | :-: | ------------------- | -----------
| **min**         |  ✓  | _DateTime_          | ...
| **max**         |  ✓  | _DateTime_          | ...
| **format**      |     | _string_            |
<small>(V) indicates options used in validation</small>

> ⚠ DateTime properties can not be multiple. (`multiple()` will always return false). Calling `set_multiple(true)` will throw an exception

## File Property

File property holds an uploadable file.

### Values

Note that only a _relative_<sup>1</sup> file path should be stored in the database.

<sup>1</sup> Relative to project's `ROOT` + the property's `upload_path`.

### File Property options

The file property adds the following concepts to the [basic property options](#basic-property-options):

| Name                  | (V) | Type        | Description
| --------------------- | :-: | ----------- | -----------
| **public_access**     |     | _bool_      |
| **upload_path**       |     | _string_    | The folder, relative to `ROOT` or `URL`, where the file will be uploaded to.
| **overwrite**         |     | _bool_      | If true, when a target file already exists it will be overwrited. If false, a new unique name will be generated (with a suffix).
| **accepted_mimeypes** |  ✓  | _string[]_  | List of accepted mimetypes. Empty / null to accept all types.
| **max_filesize**      |  ✓  | _integer_   | Maximum alowed file size, in bytes.
<small>(V) indicates options used in validation</small>

### Additional file methods

- `mimetype()` and `setMimetype()`
- `filesize()` and `setFilesize()`
- `dataUpload()`
- `fileUpload()`

### Specialized File properties

- [`AudioProperty`](#audio-file-property)
- [`ImageProperty`](#image-file-property)
- ~~VideoProperty~~

## Audio File Property

Audio property are specialized [file property](#file-property) thay may only contain audio data.

### Accepted mimetypes

The `AudioProperty` extends `FileProperty` therefore provides "accepted mimetypes".

Default accepted mimetypes are:

- `audio/mp3`
- `audio/mpeg`
- `audio/wav`
- `audio/x-wav`.

### Audio file Property options

The audio property adds the following concepts to the [file property options](#file-property-options):

| Name            | (V) | Type                | Description
| --------------- | :-: | ------------------- | -----------
| **min_length**  |  ✓  | _DateTime_          | Minimum audio length, in seconds.
| **max_length**  |  ✓  | _DateTime_          | Maximum audio length, in seconds.
<small>(V) indicates options used in validation</small>

## Image File Property

Image property are specialized [file property](#file-property) thay may only contain image data.

### Accepted mimetypes

The `ImageProperty` extends `FileProperty` therefore provides "accepted mimetypes".

Default accepted mimetypes are:

- `image/gif`
- `image/jpg`
- `image/jpeg`
- `image/pjpeg`
- `image/png`
- `image/svg+xml`

### Image file Property options

The audio property adds the following concepts to the [file property options](#file-property-options):

| Name            | (V) | Type                | Description
| --------------- | :-: | ------------------- | -----------
| **effects**     |  ✓  | _array_             | Array of `charcoal-image` effects options.
<small>(V) indicates options used in validation</small>


## Lang Property

Language properties hold a language value.

## Number Property

Number properties hold any kind of numeric data.

## Object Property

Object properties hold a reference to an external object of a certain type.

### Values

The target's `identifer` (determined by its _obj-type_'s `key`, which is typically "id") is the only thing held in the value / stored in the database. When displayed (with `display_val()`), a string representation of the object should be rendered.

### Object Property options

The object property adds the following concepts to the [basic property options](#basic-property-options):

| Name            | (V) | Type                | Description
| --------------- | :-: | ------------------- | -----------
| **obj-type**    |  ✓  | _string_            | The target object's type. In a string format that can be fetched with a `ModelFactory`. |
<small>(V) indicates options used in validation</small>

## String Property

### String Property options

The string property adds the following concepts to the basic property options:

| Name            | (V) | Type                | Description
| --------------- | :-: | ------------------- | -----------
| **max_length**  |  ✓  | _integer_           | Maximum allowed length, in (_multibytes_) characters. |
| **min_length**  |  ✓  | _integer_           | Minimum allowed length, in (_multibytes_) characters. |
| **regexp**      |  ✓  | _string_            | A validation regular expression that must be matched exactly. |
| **allow_empty** |  ✓  | _bool_              | If empty strings are allowed.
<small>(V) indicates options used in validation</small>

### String default data

By default, the `string` property is targetted at small string (a maximum length) of `255` characters

### Specialized String properties

- [`HtmlProperty`](#html-string-property)
- [`PasswordProperty`](#password-string-property)
- [`PhoneProperty`](#phone-string-property)
- [`TextProperty`](#text-string-property)

## Html String Property

HTML properties are specialized [string property](#string-property) that may only contain HTML strings (instead of plain string).

## Password String Property

Password properties are specialized [string property](#string-property) that holds (encrypted) password data.

Encryption is performed with PHP's `password_hash` function.

## Phone String Property

Phone properties are specialized [string property](#string-property) that holds a phone number.

Right now, only "north-american" phone number styles are supported.

## Text String Property

Text properties are specialized [string property](#string-property) thay typically holds longer text strings.

## Map Structure Property

Map structure properties hold complex map structure data, which can be points (markers), lines and / or polygons.
