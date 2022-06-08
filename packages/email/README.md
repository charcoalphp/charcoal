Charcoal Email
==============

Sending emails (with _PHPMailer_) and queue management.


# How to install

The preferred (and only supported) way of installing _charcoal-email_ is with **composer**:

```shell
★ composer require locomotivemtl/charcoal-email
```

## Dependencies

-   [`PHP 5.6+`](http://php.net)
    - PHP 7.3+ is highly recommended
-   [`phpmailer/phpmailer`](https://github.com/PHPMailer/PHPMailer)
-   [`locomotivemtl/charcoal-config`](https://github.com/locomotivemtl/charcoal-config)
-   [`locomotivemtl/charcoal-app`](https://github.com/locomotivemtl/charcoal-app)

## Optional dependencies

-   [`pimple/pimple`](http://pimple.sensiolabs.org/)
    -   Dependency injection Container (required for the [Service Provider](#service-provider))

> 👉 All optional depedencies are required for development. All other development dependencies, which are optional when using charcoal-email in a project, are described in the [Development](#development) section of this README file.

# Usage

```php
$email = $container['email'];
$email->setData([
    'campaign' => 'Campaign identifier'
    'to' => [
        'recipient@example.com',
        '"Some guy" <someguy@example.com>',
        [
            'name'  => 'Other guy',
            'email' => 'otherguy@example.com'
        ]
    ],
    'bcc' => 'shadow@example.com'
    'from' => '"Company inc." <company.inc@example.com>',
    'reply_to' => [
        'name' => 'Jack CEO',
        'email' => 'jack@example.com'
    ],
    'subject' => $this->translator->trans('Email subject'),
    'template_ident' => 'foo/email/default-email'
    'attachments' => [
        'foo/bar.pdf',
        'foo/baz.pdf'
    ]
]);
$email->send();

// Alternately, to send at a later date / use the queue system:
$email->queue('in 5 minutes');
```

# Email Config

The entire email system can be configured from the main app config, in the `email` config key.

```json
{
    "email": {
        "smtp": true,
        "smtp_hostname": "smtp.example.com",
        "smtp_port": 25,
        "smtp_security": "tls",
        "smtp_username": "user@example.com",
        "smtp_password": "password",

        "default_from": "webproject@example.com",
        "default_reply_to": "webproject@example.com",
        "default_track": false,
        "default_log": true
    }
}

```

# Service Provider

All email services can be quickly registered to a (`pimple`) container with `\Charcoal\Email\ServiceProvider\EmailServiceProvider`.

**Provided services:**

| Service       | Type                | Description |
| ------------- | ------------------- | ----------- |
| **email**     | `Email`<sup>1</sup>        | An email object (factory). |
| **email/factory** | `FactoryInterface`<sup>2</sup> | An email factory, to create email objects. |

<sup>1</sup> `\Charcoal\Email\Email`.<br>
<sup>2</sup> `Charcoal\Factory\FactoryInterface`.<br>


Also available are the following helpers:

| Helper Service    | Type                | Description |
| ----------------- | ------------------- | ----------- |
| **email/config**  | `EmailConfig`<sup>3</sup> | Email configuration.
| **email/view**    | `ViewInterface`<sup>4</sup>   | The view object to render email templates (`$container['view']`).

<sup>3</sup> `\Charcoal\Email\EmailConfig`.<br>
<sup>4</sup> `\Charcoal\View\ViewInterface`.<br>

> 👉 For charcoal projects, simply add this provider to your config to enable:
>
> ```json
> {
>   "service_providers": {
>       "charcoal/email/service-provider/email": {}
>   }
> }
> ```

## Service dependencies

For the _email_ service provider to work properly, the following services are expected to e registerd on the same container:

-   `config`
-   `view`

# Development

To install the development environment:

```shell
★ composer install --prefer-source
```

To run the scripts (phplint, phpcs and phpunit):

```shell
★ composer test
```

## Development dependencies

-   `phpunit/phpunit`
-   `squizlabs/php_codesniffer`
-   `satooshi/php-coveralls`

## Continuous Integration

| Service | Badge | Description |
| ------- | ----- | ----------- |
| [Travis](https://travis-ci.org/locomotivemtl/charcoal-email) | [![Build Status](https://travis-ci.org/locomotivemtl/charcoal-email.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-email) | Runs code sniff check and unit tests. Auto-generates API documentation. |
| [Scrutinizer](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-email/) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-email/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-email/?branch=master) | Code quality checker. Also validates API documentation quality. |
| [Coveralls](https://coveralls.io/github/locomotivemtl/charcoal-email) | [![Coverage Status](https://coveralls.io/repos/github/locomotivemtl/charcoal-email/badge.svg?branch=master)](https://coveralls.io/github/locomotivemtl/charcoal-email?branch=master) | Unit Tests code coverage. |
| [Sensiolabs](https://insight.sensiolabs.com/projects/54058388-3b5d-47e3-8185-f001232d31f7) | [![SensioLabsInsight](https://insight.sensiolabs.com/projects/54058388-3b5d-47e3-8185-f001232d31f7/mini.png)](https://insight.sensiolabs.com/projects/54058388-3b5d-47e3-8185-f001232d31f7) | Another code quality checker, focused on PHP. |

## Coding Style

The Charcoal-Email module follows the Charcoal coding-style:

-   [_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
-   [_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
-   [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), autoloading is therefore provided by _Composer_.
-   [_phpDocumentor_](http://phpdoc.org/) comments.
-   Read the [phpcs.xml](phpcs.xml) file for all the details on code style.

> Coding style validation / enforcement can be performed with `composer phpcs`. An auto-fixer is also available with `composer phpcbf`.

# Authors

-    [Locomotive](https://locomotive.ca)

# License

Charcoal is licensed under the MIT license. See [LICENSE](LICENSE) for details.

