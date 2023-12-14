Charcoal Email
==============

The Email package provides an integration with [PHPMailer] for sending emails.

## Installation

```shell
composer require charcoal/email
```

For Charcoal projects, the service provider can be registered from your configuration file:

```json
{
    "service_providers": {
        "charcoal/email/service-provider/email": {}
    }
}
```

## Usage

```php
use Charcoal\Email\ServiceProvider\EmailServiceProvider;
use Pimple\Container;

$container = new Container();
$container->register(new EmailServiceProvider());

$email = $container['email'];
$email->setData([
    'from' => '"Company inc." <company.inc@example.com>',
    'bcc'  => 'shadow@example.com',
    'to'   => [
        'recipient@example.com',
        '"Some guy" <someguy@example.com>',
        [
            'name'  => 'Other guy',
            'email' => 'otherguy@example.com',
        ],
    ],
    'reply_to' => [
        'name'  => 'Jack CEO',
        'email' => 'jack@example.com'
    ],
    'subject'        => $this->translator->trans('Email subject'),
    'campaign'       => 'Campaign identifier',
    'template_ident' => 'foo/email/default-email'
    'attachments'    => [
        'foo/bar.pdf',
        'foo/baz.pdf',
    ],
]);

// Dispatch immediately:
$email->send();

// Alternately, dispatch at a later date using the queue system:
$email->queue('in 5 minutes');
```

### Email Config

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

### Service Provider

All email services can be quickly registered to a service container with `\Charcoal\Email\ServiceProvider\EmailServiceProvider`.

**Provided services:**

| Service           | Type                           | Description |
| ----------------- | ------------------------------ | ----------- |
| **email**         | `Email`<sup>[1]</sup>            | An email object (factory).
| **email/factory** | `FactoryInterface`<sup>[2]</sup> | An email factory, to create email objects.

Notes:

* <sup>[1]</sup> `\Charcoal\Email\Email`.
* <sup>[2]</sup> `Charcoal\Factory\FactoryInterface`.


Also available are the following helpers:

| Helper Service    | Type                        | Description |
| ----------------- | --------------------------- | ----------- |
| **email/config**  | `EmailConfig`<sup>[3]</sup>   | Email configuration.
| **email/view**    | `ViewInterface`<sup>[4]</sup> | The view object to render email templates (`$container['view']`).

Notes:

* <sup>[3]</sup> `\Charcoal\Email\EmailConfig`.
* <sup>[4]</sup> `\Charcoal\View\ViewInterface`.

## Resources

* [Contributing](https://github.com/charcoalphp/.github/blob/main/CONTRIBUTING.md)
* [Report issues](https://github.com/charcoalphp/charcoal/issues) and
  [send pull requests](https://github.com/charcoalphp/charcoal/pulls)
  in the [main Charcoal repository](https://github.com/charcoalphp/charcoal)

[PHPMailer]: https://github.com/PHPMailer/PHPMailer
