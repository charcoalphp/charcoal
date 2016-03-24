Charcoal Email
==============

Sending emails (with PHPMailer) and queue management.

## How to install

The preferred (and only supported) way of installing _charcoal-email_ is with **composer**:

```
composer require locomotivemtl/charcoal-email`
```

## Dependencies
- [`PHP 5.5+`]](http://php.net)
- `phpmailer/phpmailer`
- [`locomotivemtl/charcoal-config`](https://github.com/locomotivemtl/charcoal-config)
- [`locomotivemtl/charcoal-app`](https://github.com/locomotivemtl/charcoal-app)

# Development

To install the development environment:

```shell
$ composer install --prefer-source
```

## Development dependencies

- `phpunit/phpunit`
- `squizlabs/php_codesniffer`
- `satooshi/php-coveralls`

## Continuous Integration

| Service | Badge | Description |
| ------- | ----- | ----------- |
| [Travis](https://travis-ci.org/locomotivemtl/charcoal-email) | [![Build Status](https://travis-ci.org/locomotivemtl/charcoal-email.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-email) | Runs code sniff check and unit tests. Auto-generates API documentation. |
| [Scrutinizer](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-email/) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-email/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-email/?branch=master) | Code quality checker. Also validates API documentation quality. |
| [Coveralls](https://coveralls.io/github/locomotivemtl/charcoal-email) | [![Coverage Status](https://coveralls.io/repos/github/locomotivemtl/charcoal-email/badge.svg?branch=master)](https://coveralls.io/github/locomotivemtl/charcoal-email?branch=master) | Unit Tests code coverage. |
| [Sensiolabs](https://insight.sensiolabs.com/projects/533b5796-7e69-42a7-a046-71342146308a) | [![SensioLabsInsight](https://insight.sensiolabs.com/projects/533b5796-7e69-42a7-a046-71342146308a/mini.png)](https://insight.sensiolabs.com/projects/533b5796-7e69-42a7-a046-71342146308a) | Another code quality checker, focused on PHP. |

## Coding Style

The Charcoal-Email module follows the Charcoal coding-style:

- [_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
- [_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
- [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), autoloading is therefore provided by _Composer_.
- [_phpDocumentor_](http://phpdoc.org/) comments.
- Read the [phpcs.xml](phpcs.xml) file for all the details on code style.

> Coding style validation / enforcement can be performed with `composer phpcs`. An auto-fixer is also available with `composer phpcbf`.

## Authors

- Mathieu Ducharme <mat@locomotive.ca>
- Chauncey McAskill <chauncey@locomotive.ca>
- Benjamin Roch <benjamin@locomotive.ca>

## Changelog

### 0.1

-Unreleased
