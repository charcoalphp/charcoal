Charcoal Property
=================

Properties define object's metadata. They provide the building blocks of the Model's definition.

Properties are defined globally for objects (_charcoal models_) in its `metadata`. They provide properties definition, storage definition, validation definition.

> The `metadata()` method is defined in `\Charcoal\Model\DescribableInterface`.
> The `properties()` method is defined in `\Charcoal\Property\DescribablePropertyInterface`.

# How to install

The preferred (and only suppported) way of installing _charcoal-property_ is with **composer**:

```shell
★ composer require locomotivemtl/charcoal-property
```

## Dependencies

-   [`PHP 5.5+`](http:///php.net)
-   [`psr/log`](http://www.php-fig.org/psr/psr-3/)
    -   A PSR-3 compliant logger should be provided to the various services / classes.
-   [`locomotivemtl/charcoal-config`](https://github.com/locomotivemtl/charcoal-config)
    -   Properties configuration and metadata.
-   [`locomotivemtl/charcoal-core`](https://github.com/locomotivemtl/charcoal-core)
    -   Also required for validator, model and more.
-   [`locomotivemtl/charcoal-factory`](https://github.com/locomotivemtl/charcoal-fatory)
    -   Dynamic object creation is provided with charcoal factories.
-   [`locomotivemtl/charcoal-image`](https://github.com/locomotivemtl/charcoal-image)
    -   For image manipulation.
-   [`locomotivemtl/charcoal-translator`](https://github.com/locomotivemtl/charcoal-translator)
    -   For localization.

# Property options

The basic property interface (API) requires / provides the following members:

| Name           | (V) | Type                | Description
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
<small>(V) indicates options used in validation</small>

> All those methods can be accessed either via the `setData()` method or with a standard PSR-1/PSR-2 getter / setter. (`foo` would have `foo()` as a getter and `setFoo()` as a setter).

### Data retrieval

- To get a normalized value, use the `parseVal($val)` method.
- To get a string-safe, displaybale value, use `displayVal($val)`.
- To get the storage-ready format, use `storageVal($val)`.

## Default validation

Validation is provided with a `Validator` object, from charcoal-core.

-   `required`
-   `unique`
-   `allow_null`

## Source and storage methods

Properties need 4 method to integrate with a SQL source:

-   `sqlEncoding()` _string_ The SQL column encoding & collation (ex: `utf8mb4`)
-   `sqlExtra()` _string_ Raw SQL string that will be appended to
-   `sqlType()` _string_ The SQL column type (ex: `VARCHAR(16)`)
-   `sqlPdoType()` _integer_ The PDO column type (ex: `PDO::PARAM_STR`)

>  Those methods are _abstract_ and therefore must be implemented in actual properties.

# Types of properties

-   [Boolean](#boolean-property)
-   [Color](#color-property)
-   ~~Date~~
    -   [DateTime](#datetime-property)
    -   ~~Day~~
    -   ~~Month~~
    -   ~~Time~~
    -   ~~Year~~
-   [File](#file-property)
    -   [Audio](#file-audio-property)
    -   [Image](#image-property)
    -   ~~Video~~
-   [Lang](#lang-property)
-   [Number](#number-property)
    -   ~~Float~~
    -   ~~Integer~~
-   [Object](#object-property)
-   [Id](#id-property)
-   [IP](#ip-property)
-   [String](#string-property)
    -   [Html](#html-string-property)
    -   [Password](#password-string-property)
    -   [Phone](#phone-string-property)
    -   [Text](#text-string-propery)
-   ~~Structure~~
    -   [MapStructure](#map-structure-property)

Retrieve

## Boolean Property

The boolean property is one of the simplest possible: it simply holds boolean (`true` / `false`) values.

### Boolean Property options

The boolean property adds the following concepts to the [basic property options](#basic-property-options):

| Name            | (V) | Type                | Description
| --------------- | :-: | ------------------- | -----------
| **false_label** |  ✓  | _Translation_       | ...
| **true_label**  |  ✓  | _Translation_       | ...
| **format**      |     | _string_            |
<small>(V) indicates options used in validation</small>

> ⚠ Boolean properties can not be multiple. (`multiple()` will always return false). Calling `setMultiple(true)` on a `boolean` property will throw an exception.

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

> ⚠ DateTime properties can not be multiple. (`multiple()` will always return false). Calling `setMultiple(true)` on a `date-time` property will throw an exception.

## ID Property

The ID property holds an ID, typically the main object identifier (unique index key).

The ID value can be generated by many **mode**:

-   `auto-increment` is the default mode. It uses the storage engine (_mysql_) auto-increment feature to keep an auto-incrementing integer index.
-   `uniqid` creates a 13-char string with PHP's `uniqid()` function.
-   `uuid` creates a 36-char string (a _RFC-4122 v4_ Universally-Unique Identifier).

### ID Property options

The ID property adds the following concepts to the [basic property options](#basic-property-options):

| Name            | (V) | Type                | Description
| --------------- | :-: | ------------------- | -----------
| **mode**        |     | _string_            | Can be `auto-increment`, `uniqid` or `uuid`. |

> ⚠ Id properties can not be multiple. (`multiple()` will always return false). Calling `setMultiple(true)` on an `id` property will throw an exception.

### ID Property custom save

Upon `save($val)`, the ID property auto-generates and ID if its mode is `uniqid` or `uuid`.

> Note: The `auto-increment` mode does not do anything on save; it relies on the storage engine / driver to implement auto-incrementation.

## IP Property

The IP property holds an IP address. Only _IPv4_ addresses are supported for now.

There is 2 different storage modes for IP:

-   `string` is the default mode. It stores the IP address like `192.168.1.1`.
-   `int` stores the IP as a _signed long integer_.

> ⚠ Ip properties can not be multiple. (`multiple()` will always return false). Calling `setMultiple(true)` on an `ip` property will throw an exception.

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

-   `mimetype()` and `setMimetype()`
-   `filesize()` and `setFilesize()`
-   `dataUpload()`
-   `fileUpload()`

### File Property Custom Save

Upon `save($val)`, the File property attempts to upload the file or create a file from data, if necessary. The uploaded file's path (_relative_) is returned.

### Specialized File properties

-   [`AudioProperty`](#audio-file-property)
-   [`ImageProperty`](#image-file-property)
-   ~~VideoProperty~~

## Audio File Property

Audio property are specialized [file property](#file-property) thay may only contain audio data.

### Accepted mimetypes

The `AudioProperty` extends `FileProperty` therefore provides "accepted mimetypes".

Default accepted mimetypes are:

-   `audio/mp3`
-   `audio/mpeg`
-   `audio/wav`
-   `audio/x-wav`.

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

-   `image/gif`
-   `image/jpg`
-   `image/jpeg`
-   `image/pjpeg`
-   `image/png`
-   `image/svg+xml`

### Image file Property options

The audio property adds the following concepts to the [file property options](#file-property-options):

| Name            | (V) | Type                | Description
| --------------- | :-: | ------------------- | -----------
| **effects**     |  ✓  | _array_             | Array of `charcoal-image` effects options.
<small>(V) indicates options used in validation</small>


## Lang Property

Language properties hold a language value.

> The `LangProperty` implements the `SelectablePropertyInterface`, but hardcode its `choices()` method to return the active language (from `[charcoal-translator](https://github.com/locomotivemtl/charcoal-translator)`).

## Number Property

Number properties hold any kind of numeric data.

## Object Property

Object properties hold a reference to an external object of a certain type.

### Values

The target's `identifer` (determined by its _obj-type_'s `key`, which is typically "id") is the only thing held in the value / stored in the database. When displayed (with `displayVal()`), a string representation of the object should be rendered.

### Object Property options

The object property adds the following concepts to the [basic property options](#basic-property-options):

| Name            | (V) | Type                | Description
| --------------- | :-: | ------------------- | -----------
| **obj-type**    |  ✓  | _string_            | The target object's type. In a string format that can be fetched with a `ModelFactory`. |
| **pattern**     |     | _string_            | The rendering pattern, used to display the object(s) when necessary.
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

-   [`HtmlProperty`](#html-string-property)
-   [`PasswordProperty`](#password-string-property)
-   [`PhoneProperty`](#phone-string-property)
-   [`TextProperty`](#text-string-property)

## Html String Property

HTML properties are specialized [string property](#string-property) that may only contain HTML strings (instead of plain string).

## Password String Property

Password properties are specialized [string property](#string-property) that holds (encrypted) password data.

Encryption is performed with PHP's `password_hash` function.

### Password Property Custom Save

Upon `save($val)`, the Password property hashes (or rehashes) the password to be stored safely (encrypted).

## Phone String Property

Phone properties are specialized [string property](#string-property) that holds a phone number.

Right now, only "north-american" phone number styles are supported.

## Text String Property

Text properties are specialized [string property](#string-property) thay typically holds longer text strings.

## Map Structure Property

Map structure properties hold complex map structure data, which can be points (markers), lines and / or polygons.

## Properties table summary, for developers

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

# Development

To install the development environment:

```shell
★ composer install --prefer-source
```

Run the code checkers and unit tests with:

```shell
★ composer test
```

## API documentation

-   The auto-generated `phpDocumentor` API documentation is available at [https://locomotivemtl.github.io/charcoal-property/docs/master/](https://locomotivemtl.github.io/charcoal-property/docs/master/)
-   The auto-generated `apigen` API documentation is available at [https://locomotivemtl.github.io/charcoal-property/apigen/master/](https://locomotivemtl.github.io/charcoal-property/apigen/master/)

## Development dependencies

-   `phpunit/phpunit`
-   `squizlabs/php_codesniffer`
-   `satooshi/php-coveralls`

## Continuous Integration

| Service | Badge | Description |
| ------- | ----- | ----------- |
| [Travis](https://travis-ci.org/locomotivemtl/charcoal-property) | [![Build Status](https://travis-ci.org/locomotivemtl/charcoal-property.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-property) | Runs code sniff check and unit tests. Auto-generates API documentaation. |
| [Scrutinizer](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-property/) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-property/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-property/?branch=master) | Code quality checker. Also validates API documentation quality. |
| [Coveralls](https://coveralls.io/github/locomotivemtl/charcoal-property) | [![Coverage Status](https://coveralls.io/repos/github/locomotivemtl/charcoal-property/badge.svg?branch=master)](https://coveralls.io/github/locomotivemtl/charcoal-property?branch=master) | Unit Tests code coverage. |
| [Sensiolabs](https://insight.sensiolabs.com/projects/f3bdff38-c300-4207-8342-da002e64a6e1) | [![SensioLabsInsight](https://insight.sensiolabs.com/projects/f3bdff38-c300-4207-8342-da002e64a6e1/mini.png)](https://insight.sensiolabs.com/projects/f3bdff38-c300-4207-8342-da002e64a6e1) | Another code quality checker, focused on PHP. |

## Coding Style

The Charcoal-Property module follows the Charcoal coding-style:

-   [_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
-   [_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
-   [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), autoloading is therefore provided by _Composer_.
-   [_phpDocumentor_](http://phpdoc.org/) comments.
-   Read the [phpcs.xml](phpcs.xml) file for all the details on code style.

> Coding style validation / enforcement can be performed with `composer phpcs`. An auto-fixer is also available with `composer phpcbf`.

## Authors

-   Mathieu Ducharme <mat@locomotive.ca>
-   Chauncey McAskill <chauncey@locomotive.ca>
-   Benjamin Roch <benjamin@locomotive.ca>

## Changelog

_Unreleased_

# License

**The MIT License (MIT)**

_Copyright © 2016 Locomotive inc._
> See [Authors](#authors).

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

