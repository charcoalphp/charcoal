Charcoal Translator
===================

# Table of content

- [How to install](#how-to-install)
    + [Dependencies](#dependencies)
- [The Translation Object](#the-translation-object)
- [The Translator Service](#the-translator-service)
- [The Locales Manager](#the-locales-manager)
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

# How to install

The preferred (and only supported) way of installing _charcoal-translator_ is with **composer**:

```shell
★ composer require locomotivemtl/charcoal-translator
```

## Dependencies

- PHP 5.6+
    + This is the last supported version of PHP.
    + `PHP 7` is also supported.
- `symfony/translation`
    + The Translator service extends the _Symfony Translator_.
- `pimple/pimple`
    + The Service Provider can be used to set up services and dependencies easily on a _Pimple Container_.
- `locomotivemtl/charcoal-config`
    + The configuration, which is used with the _Service Provider_ is defined with a _Charcoal Config_.

# The Translation Object



# The Translator Service

The Translator Service (`\Charcoal\Translator\Translator`) extends the Symfony Translator to also provide two new translation methods (`translation($val)` and `translator($val)`) which can both accept mixed arguments to return either a _Translation_ object, in the case of `translation()` or a _string_, in the case of `translate($val)`.

# The Locales Manager

# Configuration

Here is an example of configuration:

```json
"locales":{
    "languages":{
        "en":{},
        "fr":{},
        "es":{
            "active":false
        }
    },
    "default_language":"fr",
    "fallback_languages":[
        "en", 
        "fr"
    ],
    "auto_detect":true
},
"translator":{
    "loaders":[
        "xliff",
        "json",
        "php"
    ],
    "paths":[
        "l10n/",
        "vendor/locomotivemtl/charcoal-app/translations/"
    ],
    "debug":false,
    "cache_dir":"translator_cache/"
}
```

# Service Provider

# Helpers
