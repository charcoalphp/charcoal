Charcoal Translator
===================

The Transator package provides tools to internationalize Web applications with support for multilingual data and an integration with [Symfony's Translation component](https://github.com/symfony/translation).

## Installation

```shell
composer require charcoal/translator
```

For Charcoal projects, the service provider can be registered from your configuration file:

```json
{
    "service_providers": {
        "charcoal/translator/service-provider/translator": {}
    }
}
```

## Overview

### Features

#### The Translation Object

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

#### The Translator Service

[`Charcoal\Translator\Translator`][src-translator-service]

Charcoal's _Translator_ extends Symfony's [`Translator`](https://api.symfony.com/master/Symfony/Component/Translation/Translator.html) to also provide two new translation methods (`translation($val)` and `translator($val)`) which can both accept mixed arguments to return either a _Translation_ object, in the case of `translation()` or a _string_, in the case of `translate($val)`.

#### The Locales Manager

[`Charcoal\Translator\LocalesManager`][src-locales-manager]

The _Locales Manager_ is used to manage available locales / languages and keep track of current language.

#### The Parser Script

[`Charcoal\Translator\Script\TranslationParserScript`][src-translation-parser]

The _Parser Script_ is used to scrape files that contain translatable content. Add the following route to your application configuration:

```json
"scripts": {
    "charcoal/translator/parse": {
        "ident": "charcoal/translator/script/translation-parser"
    }
}
```

### Service Provider

The [`TranslatorServiceProvider`][src-translator-provider] provides services and options for translating your application into different languages.

#### Parameters

-   **locales/config**: Configuration object for defining the available languages, fallbacks, and defaults.
-   **locales/default-language**: Default language of the application, optionally the navigator's preferred language.
-   **locales/browser-language**: Accepted language from the navigator.
-   **locales/fallback-languages**: List of fallback language codes for the translator.
-   **locales/available-languages**: List of language codes from the available locales.
-   **locales/languages**: List of available language structures of the application.
-   **translator/config**: Configuration object for translation service, message catalogs, and catalog loaders.
-   **translator/translations**: Dictionary of additional translations grouped by domain and locale.

#### Services

-   **locales/manager**: An instance of [`LocalesManager`][src-locales-manager], used for handling available languages, their definitions, the default language, and tracks the current language.
-   **translator**: An instance of [`Translator`][src-translator-service], that is used for translation.
-   **translator/message-selector**: An instance of [`Symfony\Component\Translation\MessageSelector`](https://api.symfony.com/master/Symfony/Component/Translation/MessageSelector.html).
-   **translator/loader/\***: Instances of the translation [`Symfony\Component\Translation\Loader\LoaderInterface`](https://api.symfony.com/master/Symfony/Component/Translation/Loader/LoaderInterface.html).

### Configuration

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
        "vendor/charcoal/app/translations/"
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

### Middleware

The [`LanguageMiddleware`][src-translator-middleware] is available for PSR-7 applications that support middleware. The middleware detects the preferred language using the `Accept-Language` HTTP header, the URI path, query string, or host.

If you are using [charcoal/app], you can add the middleware via the application configset:

```json
"middlewares": {
    "charcoal/translator/middleware/language": {
        "active": true,
        "use_params": true,
        "param_key": "hl"
    }
}
```

Otherwise, with [Slim](https://github.com/slimphp/Slim), for example:

```php
use Charcoal\Translator\Middleware\LanguageMiddleware;
use Slim\App;

$app = new App();

// Register middleware
$app->add(new LanguageMiddleware([
    'default_language' => 'fr',
    'use_params'       => true,
    'param_key'        => 'hl',
]));
```

The middleware comes with a set of default options which can be individually overridden.

| Setting               | Type           | Default              | Description |
|:----------------------|:--------------:|:--------------------:|:------------|
| **active**            | `boolean`      | `FALSE`              | Whether to enable or disable the middleware ([charcoal/app] only).
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

### Helpers

#### TranslatorAwareTrait

[`Charcoal\Translator\TranslatorAwareTrait`][src-translator-helper]

The `TranslatorAwareTrait` is offered as convenience to avoid duplicate / boilerplate code. It simply sets and gets a `Translator` service property.

Set with `setTranslator()` and get with `translator()`. Both are protected method. (This trait has no public interface.)

## Resources

* [Contributing](https://github.com/charcoalphp/.github/blob/main/CONTRIBUTING.md)
* [Report issues](https://github.com/charcoalphp/charcoal/issues) and
  [send pull requests](https://github.com/charcoalphp/charcoal/pulls)
  in the [main Charcoal repository](https://github.com/charcoalphp/charcoal)

[src-locales-manager]:        src/Charcoal/Translator/LocalesManager.php
[src-translation-parser]:     src/Charcoal/Translator/Script/TranslationParserScript.php
[src-translation]:            src/Charcoal/Translator/Translation.php
[src-translator-helper]:      src/Charcoal/Translator/TranslatorAwareTrait.php
[src-translator-middleware]:  src/Charcoal/Translator/Middleware/LanguageMiddleware.php
[src-translator-provider]:    src/Charcoal/Translator/ServiceProvider/TranslatorServiceProvider.php
[src-translator-service]:     src/Charcoal/Translator/Translator.php
[charcoal/app]:               https://github.com/charcoalphp/app
