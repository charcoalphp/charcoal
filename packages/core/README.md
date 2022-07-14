Charcoal Core
=============

[![Build Status](https://travis-ci.org/locomotivemtl/charcoal-core.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-core)

The `charcoal-core` module contains a few core charcoal namespaces: `\Charcoal\Loader`, `\Charcoal\Model`, `\Charcoal\Source` and `\Charcoal\Validator`.

## How to Install

The preferred (and only supported) way of installing _charcoal-core_ is with **composer**:

```shell
★ composer require locomotivemtl/charcoal-core
```

For a complete, ready-to-use _Charcoal_ project, start from the [`boilerplate`](https://github.com/locomotivemtl/charcoal-project-boilerplate):

```shell
★ composer create-project locomotivemtl/charcoal-project-boilerplate:@dev --prefer-source
```

## Dependencies and Requirements

Charcoal depends on:

-   `PHP** 5.6+`
    - `PHP 7` is recommended, for performance and security.
-   `ext-pdo`
-   `ext-mbstring`
-   `psr/log`
-   `psr/cache`
-   `locomotivemtl/charcoal-config`
-   `locomotivemtl/charcoal-factory`
-   `locomotivemtl/charcoal-property`
-   `locomotivemtl/charcoal-view`

# Loader

# Model

# Source

# Validator

The validator namespace is obsolete and should not be used.
Its usage is currently being removed from everywhere in charcoal.


# Development

To install the development environment:

```shell
★ composer install --prefer-source
```

To run the tests:

```shell
★ composer test
```

## API documentation

-   The auto-generated `phpDocumentor` API documentation is available at [https://locomotivemtl.github.io/charcoal-core/docs/master/](https://locomotivemtl.github.io/charcoal-core/docs/master/)
-   The auto-generated `apigen` API documentation is available at [https://codedoc.pub/locomotivemtl/charcoal-core/master/](https://codedoc.pub/locomotivemtl/charcoal-core/master/index.html)

## Development dependencies

-   `phpunit/phpunit`
-   `squizlabs/php_codesniffer`
-   `php-coveralls/php-coveralls`

## Continuous Integration

| Service | Badge | Description |
| ------- | ----- | ----------- |
| [Travis](https://travis-ci.org/locomotivemtl/charcoal-base) | [![Build Status](https://travis-ci.org/locomotivemtl/charcoal-core.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-core) | Runs code sniff check and unit tests. Auto-generates API documentation. |
| [Scrutinizer](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-core/) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-core/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-core/?branch=master) | Code quality checker. Also validates API documentation quality. |
| [Coveralls](https://coveralls.io/github/locomotivemtl/charcoal-core) | [![Coverage Status](https://coveralls.io/repos/github/locomotivemtl/charcoal-core/badge.svg?branch=master)](https://coveralls.io/github/locomotivemtl/charcoal-core?branch=master) | Unit Tests code coverage. |
| [Sensiolabs](https://insight.sensiolabs.com/projects/ab15f6b0-2063-445e-81d7-2575b919b0ab) | [![SensioLabsInsight](https://insight.sensiolabs.com/projects/ab15f6b0-2063-445e-81d7-2575b919b0ab/mini.png)](https://insight.sensiolabs.com/projects/ab15f6b0-2063-445e-81d7-2575b919b0ab) | Another code quality checker, focused on PHP. |

## Coding Style

The charcoal-core module follows the Charcoal coding-style:

-   [_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
-   [_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
-   [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), autoloading is therefore provided by _Composer_.
-   [_phpDocumentor_](http://phpdoc.org/) comments.
-   Read the [phpcs.xml.dist](phpcs.xml.dist) file for all the details on code style.

> Coding style validation / enforcement can be performed with `composer phpcs`. An auto-fixer is also available with `composer phpcbf`.

# Authors

-   Mathieu Ducharme <mat@locomotive.ca>

# License

Charcoal is licensed under the MIT license. See [LICENSE](LICENSE) for details.

# Changelog

-   Unreleased.

## TODOs

-   Remove the dependency on charcoal-app



## Report Issues

In case you are experiencing a bug or want to request a new feature head over to the [Charcoal monorepo issue tracker](https://github.com/charcoalphp/charcoal/issues)



## Contribute

The sources of this package are contained in the Charcoal monorepo. We welcome contributions for this package on [charcoalphp/charcoal](https://github.com/charcoalphp/charcoal).
