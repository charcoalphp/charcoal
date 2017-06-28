Charcoal Translator
===================

## Table of content

- [How to install](#how-to-install)
    + [Dependencies](#dependencies)
- [The Translation Object](#the-translation-object)
- [The Translator Service](#the-translator-service)
- [The Locales Manager](#the-locales-manager)
- [The Parser Script](#the-parser-script)
- [Configuration](#configuration)
- [Service Provider](#service-provider)
- Helpers
    + [TranslatorAwareTrait](#translatorawaretrait)
- [Development](#development)
    + [Development dependencies](#development-dependencies)
    + [Continuous Integration](#continuous-integration)
    + [Coding Style](#coding-style)
    + [Authors](#authors)
    + [Changelog](#changelog)

## How to install

The preferred (and only supported) way of installing _charcoal-translator_ is with **composer**:

```shell
★ composer require locomotivemtl/charcoal-translator
```

### Dependencies

- PHP 5.6+
    + This is the last supported version of PHP.
    + `PHP 7` is also supported.
- `symfony/translation`
    + The Translator service extends the _Symfony Translator_.
- `pimple/pimple`
    + The Service Provider can be used to set up services and dependencies easily on a _Pimple Container_.
- `locomotivemtl/charcoal-config`
    + The configuration, which is used with the _Service Provider_ is defined with a _Charcoal Config_.

## The Translation Object

The Translation Object (`\Charcoal\Translator\Translation`) holds the translation data for a given string in all available languages / locales.

```php
// Get a translation object from the Translator
$translation = $container['translator']->translation([
    'en' => 'Hello World',
    'fr' => 'Bonjour'
]);

// If cast to string, the default language will be used.
echo $translation;

// Use ArrayAccess to get (or set) a translated value.
echo $translation['fr'];
$translation['fr'] => 'Bonjour le monde';

// To loop through all translations:
foreach($translation->data() as $lang => $translatedValue) {
    // ...
}
```

## The Translator Service

The Translator Service (`\Charcoal\Translator\Translator`) extends the Symfony Translator to also provide two new translation methods (`translation($val)` and `translator($val)`) which can both accept mixed arguments to return either a _Translation_ object, in the case of `translation()` or a _string_, in the case of `translate($val)`.

## The Locales Manager

The locales manager (`\Charcoal\Translator\LocalesManager`) is used to manage available locales / languages and keep track of current language.

## The Parser Script

The parser script (`\Charcoal\Translator\Script\TranslationParserScript`) is used to scrape files that contain translatable content. Add the following route to your application configuration:

```json
"scripts": {
    "charcoal/translator/parse":{
        "ident": "charcoal/translator/script/translation-parser"
    }
}
```

## Configuration

Here is an example of configuration:

```json
"locales": {
    "languages": {
        "de": {},
        "en": {},
        "es": {
            "active": false
        },
        "fr": {}
    },
    "default_language": "fr",
    "fallback_languages": [
        "en", 
        "fr"
    ],
    "auto_detect": true
},
"translator": {
    "loaders": [
        "xliff",
        "json",
        "php"
    ],
    "paths": [
        "translations/",
        "vendor/locomotivemtl/charcoal-app/translations/"
    ],
    "debug": false,
    "cache_dir": "translator_cache/",
    "translations": {
        "messages": {
            "de": {
                "hello": "Hallo {{ name }}",
                "goodbye": "Auf Wiedersehen!"
            },
            "en": {
                "hello": "Hello {{ name }}",
                "goodbye": "Goodbye!"
            },
            "es": {
                "hello": "Hallo {{ name }}",
                "goodbye": "Adios!"
            },
            "fr": {
                "hello": "Bonjour {{ name }}",
                "goodbye": "Au revoir!"
            }
        },
        "admin": {
            "fr": {
                "Save": "Enregistrer"
            }
        }
    }
}
```

## Service Provider

The `\Charcoal\Translator\TranslatorServiceProvider` registers the various options, services and dependencies on a _Pimple Container_.

It uses the main app configuration (if set) to provide:

| Container key         | Type           | Description |
| --------------------- | -------------- | ----------- |
| **locales/config**    | `LocalesConfig` | ... |
| **locales/manager**   | `LocalesManager` | ... |
| **translator/config** | `TranslatorConfig` | ... |
| **translator**        | `Translator`   | ... |


## Helpers

### TranslatorAwareTrait

The  `\Charcoal\Translator\TranslatorAwareTrait` is offered as convenience to avoid duplicate / boilerplate code. It simply sets and gets a `Translator` service property.

Set with `setTranslator()` and get with `translator()`. Both are protected method. (This trait has no public interface.)

## Development

To install the development environment:

```shell
★ composer install --prefer-source
```

To run the scripts (phplint, phpcs and phpunit):

```shell
★ composer test
```

### API documentation

-   The auto-generated `phpDocumentor` API documentation is available at [https://locomotivemtl.github.io/charcoal-translator/docs/master/](https://locomotivemtl.github.io/charcoal-translator/docs/master/)
-   The auto-generated `apigen` API documentation is available at [https://codedoc.pub/locomotivemtl/charcoal-translator/master/](https://codedoc.pub/locomotivemtl/charcoal-translator/master/index.html)

### Development dependencies

-   `phpunit/phpunit`
-   `squizlabs/php_codesniffer`
-   `satooshi/php-coveralls`

### Continuous Integration

| Service | Badge | Description |
| ------- | ----- | ----------- |
| [Travis](https://travis-ci.org/locomotivemtl/charcoal-translator) | [![Build Status](https://travis-ci.org/locomotivemtl/charcoal-translator.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-translator) | Runs code sniff check and unit tests. Auto-generates API documentation. |
| [Scrutinizer](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-translator/) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-translator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-translator/?branch=master) | Code quality checker. Also validates API documentation quality. |
| [Coveralls](https://coveralls.io/github/locomotivemtl/charcoal-translator) | [![Coverage Status](https://coveralls.io/repos/github/locomotivemtl/charcoal-translator/badge.svg?branch=master)](https://coveralls.io/github/locomotivemtl/charcoal-translator?branch=master) | Unit Tests code coverage. |
| [Sensiolabs](https://insight.sensiolabs.com/projects/2758c820-e73a-4d0e-b746-552a3e3a92fa) | [![SensioLabsInsight](https://insight.sensiolabs.com/projects/2758c820-e73a-4d0e-b746-552a3e3a92fa/mini.png)](https://insight.sensiolabs.com/projects/2758c820-e73a-4d0e-b746-552a3e3a92fa) | Another code quality checker, focused on PHP. |

### Coding Style

The charcoal-translator module follows the Charcoal coding-style:

-   [_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
-   [_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
-   [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), autoloading is therefore provided by _Composer_.
-   [_phpDocumentor_](http://phpdoc.org/) comments.
-   Read the [phpcs.xml](phpcs.xml) file for all the details on code style.

> Coding style validation / enforcement can be performed with `composer phpcs`. An auto-fixer is also available with `composer phpcbf`.

## Authors

-   Mathieu Ducharme, mat@locomotive.ca
-   Chauncey McAskill
-   Locomotive Inc.

## Changelog

_Unreleased_

## License

**The MIT License (MIT)**

_Copyright © 2017 Locomotive inc._
> See [Authors](#authors).

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
