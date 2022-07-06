Charcoal Cache
==============

[![License][badge-license]][charcoal-cache]
[![Latest Stable Version][badge-version]][charcoal-cache]
[![Code Quality][badge-scrutinizer]][dev-scrutinizer]
[![Coverage Status][badge-coveralls]][dev-coveralls]
[![SensioLabs Insight][badge-sensiolabs]][dev-sensiolabs]
[![Build Status][badge-travis]][dev-travis]

A [Charcoal][charcoal-app] service provider for the [Stash Cache Library][stash].



## Table of Contents

-   [Installation](#installation)
    -   [Dependencies](#dependencies)
    -   [Dependents](#dependents)
-   [Service Provider](#service-provider)
    -   [Parameters](#parameters)
    -   [Services](#services)
-   [Configuration](#configuration)
    -   [Pool Configuration](#pool-configuration)
    -   ~~[Driver Configuration](#driver-configuration)~~
-   [Usage](#usage)
-   [Middleware](#middleware)
-   [Helpers](#helpers)
    -   [CachePoolAwareTrait](#cachepoolawaretrait)
-   [Development](#development)
    -  [API Documentation](#api-documentation)
    -  [Development Dependencies](#development-dependencies)
    -  [Coding Style](#coding-style)
-   [Credits](#credits)
-   [License](#license)
- [Report Issues](#report-issues)
- [Contribute](#contribute)


## Installation

1.  The preferred (and only supported) method is with Composer:

    ```shell
    $ composer require locomotivemtl/charcoal-cache
    ```

2.  Add the service provider and configure the default caching service via the application configset:

    ```json
    "service_providers": {
        "charcoal/cache/service-provider/cache": {}
    },

    "cache": {
        "prefix": "foobar",
        "types": [ "apc", "memcache", "redis" ]
    }
    ```
    
    or via the service container:
    
    ```php
    $container->register(new \Charcoal\Cache\ServiceProvider\CacheServiceProvider());
    
    $container['cache/config'] = new \Charcoal\Cache\CacheConfig([
		'prefix' => 'foobar',
		'types'  => [ 'apc', 'memcache', 'redis' ],
	]);
    ```

If you are using [_locomotivemtl/charcoal-app_][charcoal-app], the [`CacheServiceProvider`][cache-provider] is automatically registered by the [`AppServiceProvider`][app-provider].



### Dependencies

#### Required

-   [**PHP 5.6+**](https://php.net): _PHP 7_ is recommended.
-   [**tedivm/stash**][stash]: PSR-6 compliant caching library.
-   [**pimple/pimple**][pimple]: PSR-11 compliant service container and provider library.
-   [**locomotivemtl/charcoal-config**][charcoal-config]: For configuring the caching service.

#### PSR

-   [**PSR-3**][psr-3]: Common interface for logging libraries. Supported by Stash.
-   [**PSR-6**][psr-6]: Common interface for caching libraries. Fulfilled by Stash.
-   [**PSR-7**][psr-7]: Common interface for HTTP messages. Followed by [`CacheMiddleware`][cache-middleware].
-   [**PSR-11**][psr-11]: Common interface for dependency containers. Fulfilled by Pimple.

### Dependents

-   [**locomotivemtl/charcoal-admin**][charcoal-admin]: Admin interface for Charcoal applications.
-   [**locomotivemtl/charcoal-app**][charcoal-app]: PSR-7 compliant framework for web applications.  
    For caching HTTP responses via the [`CacheMiddleware`][cache-middleware].
-   [**locomotivemtl/charcoal-core**][charcoal-core]: Collection, model, metadata, and database library.  
    For caching object data and model metadata.



## Service Provider

### Parameters

-   **cache/available-drivers**: Collection of registered cache drivers that are supported by this system (via [`Stash\DriverList`][stash-drivers]).



### Services

-   **cache/config**: Configuration object for the caching service.  
    See [Pool Configuration](#pool-configuration) for available options.
-   **cache/drivers**: Collection of cache driver instances (as a service container) which uses `cache/available-drivers`.  
    These drivers are pre-configured:
    -   **file**: [FileSystem](https://www.stashphp.com/Drivers.html#filesystem)
    -   **db**: [SQLite](https://www.stashphp.com/Drivers.html#sqlite)
    -   **apc**: [APC](https://www.stashphp.com/Drivers.html#apc)
    -   **memcache**: [Memcached](https://www.stashphp.com/Drivers.html#memcached)
    -   **redis**: [Redis](https://www.stashphp.com/Drivers.html#redis)
    -   **memory**: [Ephemeral](https://www.stashphp.com/Drivers.html#ephemeral) (Runtime Only)
    -   **noop**: Blackhole (NULL caching driver)
-   **cache/builder**: Instance of [`CacheBuilder`][cache-builder] that is used to build a cache pool.
-   **cache/driver**: Reference to the Stash cache driver used by `cache`. Defaults to "memory".
-   **cache**: Main instance of the Stash cache pool which uses `cache/driver` and `cache/config.prefix`.



## Configuration

### Pool Configuration

Each pool comes with a set of default options which can be individually overridden.

| Setting         | Type       | Default    | Description |
|:----------------|:----------:|:----------:|:------------|
| **active**      | `boolean`  | `TRUE`     | Whether to enable or disable the cache service.
| **prefix**      | `string`   | `charcoal` | Name of the main Stash pool.
| **types**       | `string[]` | `memory`   | List of cache drivers to choose from for the main Stash pool. Defaults to "memory".
| **default_ttl** | `integer`  | 1 week     | Default time-to-live (in seconds) for a cached item. Currently, only used by the APC driver (`cache/drivers.apc`).



### Driver Configuration

~~Each driver comes with a set of default options which can be individually overridden.~~

—N/A—



## Usage

Just fetch the default cache pool service:

```php
$pool = $this->container->get('cache');
```

Or a custom-defined cache pool:

```php
// Create a Stash pool with the Memcached driver and a custom namespace.
$pool1 = $this->container->get('cache/builder')->build('memcache', 'altcache');

// Create a custom Stash pool with the FileSystem driver and custom features.
$pool2 = $this->container->get('cache/builder')->build('file', [
    'namespace'  => 'mycache',
    'logger'     => $this->container->get('logger.custom_logger'),
    'pool_class' => \MyApp\Cache\Pool::class,
    'item_class' => \MyApp\Cache\Item::class,
]);

// Create a Stash pool with the "memory" cache driver.
$pool3 = new \Stash\Pool($container['cache/drivers']['memory']);
```

Then you can use the cache service directly:

```php
// Get a Stash object from the cache pool.
$item = $pool->getItem("/user/{$userId}/info");

// Get the data from it, if any happens to be there.
$userInfo = $item->get();

// Check to see if the cache missed, which could mean that it either
// didn't exist or was stale.
if ($item->isMiss()) {
    // Run the relatively expensive code.
    $userInfo = loadUserInfoFromDatabase($userId);

    // Set the new value in $item.
    $item->set($userInfo);

    // Store the expensive code so the next time it doesn't miss.
    $pool->save($item);
}

return $userInfo;
```

See the [Stash documentation](stash-docs) for more information on using the cache service.



## Middleware

The [`CacheMiddleware`][cache-middleware] is available for PSR-7 applications that support middleware. The middleware saves the HTTP response body and headers into a [PSR-6 cache pool](psr-6) and returns that cached response if still valid.

If you are using [_locomotivemtl/charcoal-app_][charcoal-app], you can add the middleware via the application configset:

```json
"middlewares": {
    "charcoal/cache/middleware/cache": {
        "active": true,
        "methods": [ "GET", "HEAD" ]
    }
}
```

Otherwise, with [Slim][slim], for example:

```php
$app = new \Slim\App();

// Register middleware
$app->add(new \Charcoal\Cache\Middleware\CacheMiddleware([
    'cache'   => new \Stash\Pool(),
    'methods' => [ 'GET', 'HEAD' ],
]));
```

The middleware comes with a set of default options which can be individually overridden.

| Setting            | Type                     | Default     | Description |
|:-------------------|:------------------------ |:-----------:|:------------|
| **active**         | `boolean`                | `FALSE`     | Whether to enable or disable the middleware ([_locomotivemtl/charcoal-app_][charcoal-app] only).
| **cache**          | `CacheItemPoolInterface` | `cache`     | Required; The main Stash pool.
| **ttl**            | `string[]`               | 1 week      | Time-to-live (in seconds) for a cached response.
| **methods**        | `string[]`               | `GET`       | Accepted HTTP method(s) to cache the response.
| **status_codes**   | `integer[]`              | 200         | Accepted HTTP status code(s) to cache the response.
| **included_path**  | `string[]`               | `*`         | Accepted URI paths for caching the response.
| **excluded_path**  | `string[]`               | `^/admin\b` | Rejected URI paths for caching the response.
| **included_query** | `string[]`               | `NULL`      | Accepted query parameters for caching the response.
| **excluded_query** | `string[]`               | `NULL`      | Rejected query parameters for caching.
| **ignored_query**  | `string[]`               | `NULL`      | Ignored query parameters for caching the response.

#### By Default

All HTTP responses are cached unless:

1.  the request method is not GET
2.  the request URI path starts with `/admin…`
3.  the request URI contains a query string
4.  the response is not OK (200)

#### Ignoring Query Strings

If query strings don't affect the server's response, you can permit caching of requests by ignoring all query parameters:

```json
"ignored_query": "*"
```

or some of them:

```json
"ignored_query": [ "sort", "theme" ]
```



## Helpers

### CachePoolAwareTrait

The [`CachePoolAwareTrait`][cache-helper] is offered as a convenience to avoid duplicate / boilerplate code. It simply sets and gets an instance of `\Psr\Cache\CacheItemPoolInterface`.

Assign a cache pool with `setCachePool()` and retrieve it with `cachePool()`.  

Both methods are protected; this trait has no public interface.



## Development

To install the development environment:

```shell
$ composer install
```

To run the scripts (phplint, phpcs, and phpunit):

```shell
$ composer test
```



### API Documentation

-   The auto-generated `phpDocumentor` API documentation is available at:  
    [https://locomotivemtl.github.io/charcoal-cache/docs/master/](https://locomotivemtl.github.io/charcoal-cache/docs/master/)
-   The auto-generated `apigen` API documentation is available at:  
    [https://codedoc.pub/locomotivemtl/charcoal-cache/master/](https://codedoc.pub/locomotivemtl/charcoal-cache/master/index.html)



### Development Dependencies

-   [php-coveralls/php-coveralls][phpcov]
-   [phpunit/phpunit][phpunit]
-   [squizlabs/php_codesniffer][phpcs]



### Coding Style

The charcoal-cache module follows the Charcoal coding-style:

-   [_PSR-1_][psr-1]
-   [_PSR-2_][psr-2]
-   [_PSR-4_][psr-4], autoloading is therefore provided by _Composer_.
-   [_phpDocumentor_](http://phpdoc.org/) comments.
-   [phpcs.xml.dist](phpcs.xml.dist) and [.editorconfig](.editorconfig) for coding standards.

> Coding style validation / enforcement can be performed with `composer phpcs`. An auto-fixer is also available with `composer phpcbf`.



## Credits

-   [Mathieu Ducharme](https://github.com/mducharme)
-   [Chauncey McAskill](https://github.com/mcaskill)
-   [Locomotive](https://locomotive.ca/)



## License

-   Charcoal is licensed under the MIT license. See [LICENSE](LICENSE) for details.
-   Stash is licensed under the BSD License. See the [LICENSE][stash-license] file for details.



## Report Issues

In case you are experiencing a bug or want to request a new feature head over to the [Charcoal monorepo issue tracker](https://github.com/charcoalphp/charcoal/issues)



## Contribute

The sources of this package are contained in the Charcoal monorepo. We welcome contributions for this package on [charcoalphp/charcoal](https://github.com/charcoalphp/charcoal).


[cache-middleware]: src/Charcoal/Cache/Middleware/CacheMiddleware.php
[cache-provider]:   src/Charcoal/Cache/ServiceProvider/CacheServiceProvider.php
[cache-helper]:     src/Charcoal/Cache/CachePoolAwareTrait.php
[cache-builder]:    src/Charcoal/Cache/CacheBuilder.php
[cache-config]:     src/Charcoal/Cache/CacheConfig.php
[app-provider]:     https://github.com/locomotivemtl/charcoal-app/blob/0.8.0/src/Charcoal/App/ServiceProvider/AppServiceProvider.php

[pimple]:           https://packagist.org/packages/pimple/pimple
[slim]:             https://packagist.org/packages/slim/slim
[stash]:            https://packagist.org/packages/tedivm/stash
[phpunit]:          https://packagist.org/packages/phpunit/phpunit
[phpcs]:            https://packagist.org/packages/squizlabs/php_codesniffer
[phpcov]:           https://packagist.org/packages/php-coveralls/php-coveralls
[stash-drivers]:    https://github.com/tedious/Stash/blob/v0.14.2/src/Stash/DriverList.php
[stash-docs]:       https://www.stashphp.com/
[stash-license]:    https://github.com/tedious/Stash/blob/v0.14.2/LICENSE

[dev-scrutinizer]:    https://scrutinizer-ci.com/g/locomotivemtl/charcoal-cache/
[dev-coveralls]:      https://coveralls.io/r/locomotivemtl/charcoal-cache
[dev-sensiolabs]:     https://insight.sensiolabs.com/projects/---
[dev-travis]:         https://travis-ci.org/locomotivemtl/charcoal-cache

[badge-license]:      https://img.shields.io/packagist/l/locomotivemtl/charcoal-cache.svg?style=flat-square
[badge-version]:      https://img.shields.io/packagist/v/locomotivemtl/charcoal-cache.svg?style=flat-square
[badge-scrutinizer]:  https://img.shields.io/scrutinizer/g/locomotivemtl/charcoal-cache.svg?style=flat-square
[badge-coveralls]:    https://img.shields.io/coveralls/locomotivemtl/charcoal-cache.svg?style=flat-square
[badge-sensiolabs]:   https://img.shields.io/sensiolabs/i/---.svg?style=flat-square
[badge-travis]:       https://img.shields.io/travis/locomotivemtl/charcoal-cache.svg?style=flat-square

[charcoal-admin]:  https://packagist.org/packages/locomotivemtl/charcoal-admin
[charcoal-app]:    https://packagist.org/packages/locomotivemtl/charcoal-app
[charcoal-cache]:  https://packagist.org/packages/locomotivemtl/charcoal-cache
[charcoal-core]:   https://packagist.org/packages/locomotivemtl/charcoal-core
[charcoal-config]: https://packagist.org/packages/locomotivemtl/charcoal-config

[psr-1]:  https://www.php-fig.org/psr/psr-1/
[psr-2]:  https://www.php-fig.org/psr/psr-2/
[psr-3]:  https://www.php-fig.org/psr/psr-3/
[psr-4]:  https://www.php-fig.org/psr/psr-4/
[psr-6]:  https://www.php-fig.org/psr/psr-6/
[psr-7]:  https://www.php-fig.org/psr/psr-7/
[psr-11]: https://www.php-fig.org/psr/psr-11/
