Charcoal Translator
===================

[![License][badge-license]][charcoal-translator]
[![Latest Stable Version][badge-version]][charcoal-translator]
[![Code Quality][badge-scrutinizer]][dev-scrutinizer]
[![Coverage Status][badge-coveralls]][dev-coveralls]
[![SensioLabs Insight][badge-sensiolabs]][dev-sensiolabs]
[![Build Status][badge-travis]][dev-travis]

A [Charcoal][charcoal-app] service provider for the [Symfony Translation component][symfony/translation].

Provides internationalization (I18N) tools for translating messages and managing locales in multilingual applications.



## Table of Contents

-   [Installation](#installation)
    -   [Dependencies](#dependencies)
-   [Features](#features)
    -   [The Translation Object](#the-translation-object)
    -   [The Translator Service](#the-translator-service)
    -   [The Locales Manager](#the-locales-manager)
    -   [The Parser Script](#the-parser-script)
-   [Service Provider](#service-provider)
    -   [Parameters](#parameters)
    -   [Services](#services)
-   [Configuration](#configuration)
-   [Middleware](#middleware)
-   [Helpers](#helpers)
    -   [TranslatorAwareTrait](#translatorawaretrait)
-   [Development](#development)
    -   [Development dependencies](#development-dependencies)
    -   [Continuous Integration](#continuous-integration)
    -   [Coding Style](#coding-style)
-   [Credits](#credits)
-   [License](#license)
- [Report Issues](#report-issues)
- [Contribute](#contribute)


## Installation

1.  The preferred (and only supported) method is with Composer:

    ```shell
    ★ composer require locomotivemtl/charcoal-translator
    ```

2.  Add the service provider and configure the default translator / locale services via the application configset:

    ```js
    "service_providers": {
        "charcoal/translator/service-provider/translator": {}
    },

    "translator": {
        // …
    },

    "locales": {
        // …
    }
    ```
    
    or via the service container:
    
    ```php
    $container->register(new \Charcoal\Translator\ServiceProvider\TranslatorServiceProvider());
    
    $container['translator/config'] = new \Charcoal\Translator\TranslatorConfig([
        // …
    ]);

    $container['locales/config'] = new \Charcoal\Translator\LocalesConfig([
        // …
    ]);
    ```

If you are using [_locomotivemtl/charcoal-app_][charcoal-app], the [`TranslatorServiceProvider`][translator-provider] is automatically registered by the [`AppServiceProvider`][src-app-provider].



### Dependencies

#### Required

-   [**PHP 5.6+**](https://php.net): _PHP 7_ is recommended.
-   [**symfony/translation**][symfony/translation]: Translation component which Charcoal's translator service extends.
-   [**pimple/pimple**][pimple]: PSR-11 compliant service container and provider library.
-   [**locomotivemtl/charcoal-config**][charcoal-config]: For configuring the translator service and locales.

#### PSR

-   [**PSR-7**][psr-7]: Common interface for HTTP messages. Followed by [`LanguageMiddleware`][src-translator-middleware].
-   [**PSR-11**][psr-11]: Common interface for dependency containers. Fulfilled by Pimple.

### Dependents

-   [**locomotivemtl/charcoal-admin**][charcoal-admin]: Admin interface for Charcoal applications.
-   [**locomotivemtl/charcoal-app**][charcoal-app]: PSR-7 compliant framework for web applications.  
    For resolving the current locale via the [`LanguageMiddleware`][src-translator-middleware].
-   [**locomotivemtl/charcoal-cms**][charcoal-cms]: Pre-designed models and basic utilities for content management (pages, news, events).  
    For supporting multilingual model properties and localizing model descriptors.
-   [**locomotivemtl/charcoal-property**][charcoal-property]: Model property values and metadata.  
    For supporting multilingual values and localizing property descriptors.



## Features

### The Translation Object

[`Charcoal\Translator\Translation`][src-translation]

The _Translation Object_ holds the translation data for a given string in all available languages / locales.

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
foreach ($translation->data() as $lang => $translatedValue) {
    // ...
}
```



### The Translator Service

[`Charcoal\Translator\Translator`][src-translator-service]

Charcoal's _Translator_ extends Symfony's [`Translator`](https://api.symfony.com/master/Symfony/Component/Translation/Translator.html) to also provide two new translation methods (`translation($val)` and `translator($val)`) which can both accept mixed arguments to return either a _Translation_ object, in the case of `translation()` or a _string_, in the case of `translate($val)`.



### The Locales Manager

[`Charcoal\Translator\LocalesManager`][src-locales-manager]

The _Locales Manager_ is used to manage available locales / languages and keep track of current language.



### The Parser Script

[`Charcoal\Translator\Script\TranslationParserScript`][src-translation-parser]

The _Parser Script_ is used to scrape files that contain translatable content. Add the following route to your application configuration:

```json
"scripts": {
    "charcoal/translator/parse": {
        "ident": "charcoal/translator/script/translation-parser"
    }
}
```



## Service Provider

The [`TranslatorServiceProvider`][src-translator-provider] provides services and options for translating your application into different languages.

### Parameters

-   **locales/config**: Configuration object for defining the available languages, fallbacks, and defaults.
-   **locales/default-language**: Default language of the application, optionally the navigator's preferred language.
-   **locales/browser-language**: Accepted language from the navigator.
-   **locales/fallback-languages**: List of fallback language codes for the translator.
-   **locales/available-languages**: List of language codes from the available locales.
-   **locales/languages**: List of available language structures of the application.
-   **translator/config**: Configuration object for translation service, message catalogs, and catalog loaders.
-   **translator/translations**: Dictionary of additional translations grouped by domain and locale.



### Services

-   **locales/manager**: An instance of [`LocalesManager`][src-locales-manager], used for handling available languages, their definitions, the default language, and tracks the current language.
-   **translator**: An instance of [`Translator`][src-translator-service], that is used for translation.
-   **translator/message-selector**: An instance of [`Symfony\Component\Translation\MessageSelector`](https://api.symfony.com/master/Symfony/Component/Translation/MessageSelector.html).
-   **translator/loader/\***: Instances of the translation [`Symfony\Component\Translation\Loader\LoaderInterface`](https://api.symfony.com/master/Symfony/Component/Translation/Loader/LoaderInterface.html).



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
    "cache_dir": "cache/translation/",
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



## Middleware

The [`LanguageMiddleware`][src-translator-middleware] is available for PSR-7 applications that support middleware. The middleware detects the preferred language using the `Accept-Language` HTTP header, the URI path, query string, or host.

If you are using [_locomotivemtl/charcoal-app_][charcoal-app], you can add the middleware via the application configset:

```json
"middlewares": {
    "charcoal/translator/middleware/language": {
        "active": true,
        "use_params": true,
        "param_key": "hl"
    }
}
```

Otherwise, with [Slim][slim], for example:

```php
$app = new \Slim\App();

// Register middleware
$app->add(new \Charcoal\Translator\Middleware\LanguageMiddleware([
    'default_language' => 'fr',
    'use_params'       => true,
    'param_key'        => 'hl',
]));
```

The middleware comes with a set of default options which can be individually overridden.

| Setting               | Type           | Default              | Description |
|:----------------------|:--------------:|:--------------------:|:------------|
| **active**            | `boolean`      | `FALSE`              | Whether to enable or disable the middleware ([_locomotivemtl/charcoal-app_][charcoal-app] only).
| **default_language**  | `string`       | `null`               | The default language to use if no other languages is choosen.
| **browser_language**  | `string`       | `null`               | The client's preferred language (`Accept-Language`).
| **use_browser**       | `boolean`      | `true`               | Whether to use `browser_language` as the default language.
| **use_path**          | `boolean`      | `true`               | Whether to lookup the HTTP request's URI path for a language code.
| **excluded_path**     | `string|array` | `^/admin\b`          | One or more RegEx patterns to ignore from localization, when matching the URI path.
| **path_regexp**       | `string|array` | `^/([a-z]{2})\b`     | One or more RegEx patterns to include from localization, when matching the URI path.
| **use_params**        | `boolean`      | `false`              | Whether to lookup the HTTP request's URI query string for a language code.
| **param_key**         | `string|array` | `current_language`   | One or more RegEx patterns to include from localization, when matching the query string keys.
| **use_session**       | `boolean`      | `true`               | Whether to lookup the client's PHP session for a preferred language.
| **session_key**       | `string|array` | `current_language`   | One or more RegEx patterns to include from localization, when matching the session keys.
| **use_host**          | `boolean`      | `false`              | Whether to lookup the server host for a language code.
| **host_map**          | `string|array` | `[]`                 | One or more RegEx patterns to include from localization, when matching the host.
| **set_locale**        | `boolean`      | `true`               | Whether to set the environment's locale.



## Helpers

### TranslatorAwareTrait

[`Charcoal\Translator\TranslatorAwareTrait`][src-translator-helper]

The  `TranslatorAwareTrait` is offered as convenience to avoid duplicate / boilerplate code. It simply sets and gets a `Translator` service property.

Set with `setTranslator()` and get with `translator()`. Both are protected method. (This trait has no public interface.)



## Development

To install the development environment:

```shell
★ composer install --prefer-source
```

To run the scripts (phplint, phpcs and phpunit):

```shell
★ composer tests
```



### API Documentation

-   The auto-generated `phpDocumentor` API documentation is available at:  
    [https://locomotivemtl.github.io/charcoal-translator/docs/master/](https://locomotivemtl.github.io/charcoal-translator/docs/master/)
-   The auto-generated `apigen` API documentation is available at:  
    [https://codedoc.pub/locomotivemtl/charcoal-translator/master/](https://codedoc.pub/locomotivemtl/charcoal-translator/master/index.html)



### Development Dependencies

-   [php-coveralls/php-coveralls][phpcov]
-   [phpunit/phpunit][phpunit]
-   [squizlabs/php_codesniffer][phpcs]



### Coding Style

The charcoal-translator module follows the Charcoal coding-style:

-   [_PSR-1_][psr-1]
-   [_PSR-2_][psr-2]
-   [_PSR-4_][psr-4], autoloading is therefore provided by _Composer_.
-   [_phpDocumentor_](http://phpdoc.org/) comments.
-   [phpcs.xml.dist](phpcs.xml.dist) and [.editorconfig](.editorconfig) for coding standards.

> Coding style validation / enforcement can be performed with `composer phpcs`. An auto-fixer is also available with `composer phpcbf`.



## Credits

-   [Mathieu Ducharme](https://github.com/mducharme)
-   [Chauncey McAskill](https://github.com/mcaskill)
-   [Benjamin Roch](https://github.com/beneroch)
-   [Locomotive](https://locomotive.ca/)



## License

Charcoal is licensed under the MIT license. See [LICENSE](LICENSE) for details.



## Report Issues

In case you are experiencing a bug or want to request a new feature head over to the [Charcoal monorepo issue tracker](https://github.com/charcoalphp/charcoal/issues)



## Contribute

The sources of this package are contained in the Charcoal monorepo. We welcome contributions for this package on [charcoalphp/charcoal](https://github.com/charcoalphp/charcoal).



[src-locales-manager]:        src/Charcoal/Translator/LocalesManager.php
[src-translation-parser]:     src/Charcoal/Translator/Script/TranslationParserScript.php
[src-translation]:            src/Charcoal/Translator/Translation.php
[src-translator-helper]:      src/Charcoal/Translator/TranslatorAwareTrait.php
[src-translator-middleware]:  src/Charcoal/Translator/Middleware/LanguageMiddleware.php
[src-translator-provider]:    src/Charcoal/Translator/ServiceProvider/TranslatorServiceProvider.php
[src-translator-service]:     src/Charcoal/Translator/Translator.php
[src-app-provider]:           https://github.com/locomotivemtl/charcoal-app/blob/0.8.0/src/Charcoal/App/ServiceProvider/AppServiceProvider.php


[dev-scrutinizer]:    https://scrutinizer-ci.com/g/locomotivemtl/charcoal-translator/
[dev-coveralls]:      https://coveralls.io/github/locomotivemtl/charcoal-translator
[dev-sensiolabs]:     https://insight.sensiolabs.com/projects/2758c820-e73a-4d0e-b746-552a3e3a92fa
[dev-travis]:         https://travis-ci.org/locomotivemtl/charcoal-translator

[badge-license]:      https://img.shields.io/packagist/l/locomotivemtl/charcoal-translator.svg?style=flat-square
[badge-version]:      https://img.shields.io/packagist/v/locomotivemtl/charcoal-translator.svg?style=flat-square
[badge-scrutinizer]:  https://img.shields.io/scrutinizer/g/locomotivemtl/charcoal-translator.svg?style=flat-square
[badge-coveralls]:    https://img.shields.io/coveralls/locomotivemtl/charcoal-translator.svg?style=flat-square
[badge-sensiolabs]:   https://img.shields.io/sensiolabs/i/2758c820-e73a-4d0e-b746-552a3e3a92fa.svg?style=flat-square
[badge-travis]:       https://img.shields.io/travis/locomotivemtl/charcoal-translator.svg?style=flat-square

[charcoal-admin]:        https://packagist.org/packages/locomotivemtl/charcoal-admin
[charcoal-app]:          https://packagist.org/packages/locomotivemtl/charcoal-app
[charcoal-cms]:          https://packagist.org/packages/locomotivemtl/charcoal-cms
[charcoal-config]:       https://packagist.org/packages/locomotivemtl/charcoal-config
[charcoal-property]:     https://packagist.org/packages/locomotivemtl/charcoal-property
[charcoal-translator]:   https://packagist.org/packages/locomotivemtl/charcoal-translator

[pimple]:                https://packagist.org/packages/pimple/pimple
[slim]:                  https://packagist.org/packages/slim/slim
[phpunit]:               https://packagist.org/packages/phpunit/phpunit
[phpcs]:                 https://packagist.org/packages/squizlabs/php_codesniffer
[phpcov]:                https://packagist.org/packages/php-coveralls/php-coveralls
[symfony/translation]:   https://packagist.org/packages/symfony/translation

[psr-1]:  https://www.php-fig.org/psr/psr-1/
[psr-2]:  https://www.php-fig.org/psr/psr-2/
[psr-4]:  https://www.php-fig.org/psr/psr-4/
[psr-7]:  https://www.php-fig.org/psr/psr-7/
[psr-11]: https://www.php-fig.org/psr/psr-11/
