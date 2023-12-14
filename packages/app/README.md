Charcoal App
============

The App package provides integration with [Slim] and [Pimple] for building user-facing Web applications and APIs.

## Installation

```shell
composer require charcoal/app
```

## Overview

The App package is a collection of _modules_, _routes_ (`templates`, `actions` and `scripts`), _handlers_, and _services_ tied together with a _config_, a _service container_, and _service providers_.

The framework features (internally and externally) the following:

* PSR-3 logger
* PSR-6 cache system<sup>[†]</sup>
* PSR-7 kernel (web, API, CLI)
* PSR-11 container
* Translation layer<sup>[†]</sup>
* File system layer
* Database layer
* View layer<sup>[†]</sup>

Notes:

* <sup>[†]</sup>  Provided by external Charcoal components.

### Components

The main components of the Charcoal App are:

* [Config](docs/components.md#config-component)
* [App](docs/components.md#app-compoment)
* ~~[Module](docs/components.md#module-component)~~
* [Routes & Request Controllers](docs/components.md#routes--request-controllers)
  * [Action](docs/components.md#action-request-controller)
  * [Script](docs/components.md#script-request-controller)
  * [Template](docs/components.md#template-request-controller)
  * [Route API](docs/components.md#route-api)
* [Routable Objects](docs/components.md#routable-objects)
* [Charcoal Binary](docs/components.md#charcoal-binary)
* [PHPUnit Tests](docs/components.md#phpunit-tests)

Learn more about [components](docs/components.md).

### Service Providers

Dependencies and extensions are handled by a dependency container, using [Pimple][pimple], which can be defined via _service providers_ (`Pimple\ServiceProviderInterface`).

##### Included Providers

The Charcoal App comes with several providers out of the box. All of these are within the `Charcoal\App\ServiceProvider` namespace:

* [`AppServiceProvider`](docs/providers.md#app-service-provider)
* [`DatabaseServicePovider`](docs/providers.md#database-service-provider)
* [`FilesystemServiceProvider`](docs/providers.md#filesystem-service-provider)
* [`LoggerServiceProvider`](docs/providers.md#logger-service-provider)

##### External Providers

The Charcoal App requires a few providers from independent components. The following use their own namespace and are automatically injected via the `AppServiceProvider`:

* [`CacheServiceProvider`](docs/providers.md#cache-service-provider)
* [`TranslatorServiceProvider`](docs/providers.md#translator-service-provider)
* [`ViewServiceProvider`](docs/providers.md#view-service-provider)

Learn more about [service providers](docs/providers.md).

## Usage

Typical front-controller ([`www/index.php`](www/index.php)):

```php
use Charcoal\App\App;
use Charcoal\App\AppConfig;
use Charcoal\App\AppContainer;

include '../vendor/autoload.php';

$config = new AppConfig();
$config->addFile(__DIR__.'/../config/config.php');
$config->set('ROOT', dirname(__DIR__) . '/');

// Create container and configure it (with charcoal/config)
$container = new AppContainer([
    'settings' => [
        'displayErrorDetails' => true,
    ],
    'config' => $config,
]);

// Charcoal / Slim is the main app
$app = App::instance($container);
$app->run();
```

For a complete project example using `charcoal/app`, see the [charcoal/boilerplate](https://github.com/charcoalphp/boilerplate).

## Resources

* [Contributing](https://github.com/charcoalphp/.github/blob/main/CONTRIBUTING.md)
* [Report issues](https://github.com/charcoalphp/charcoal/issues) and
  [send pull requests](https://github.com/charcoalphp/charcoal/pulls)
  in the [main Charcoal repository](https://github.com/charcoalphp/charcoal)

[Pimple]: https://github.com/silexphp/Pimple
[Slim]:   https://github.com/slimphp/Slim
