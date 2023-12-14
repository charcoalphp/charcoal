Charcoal Cache
==============

The Cache package provides an integration with [Stash] for caching the results of expensive tasks.

## Installation

```shell
composer require charcoal/cache
```

For Charcoal projects, the service provider can be registered from your configuration file:

```json
{
    "service_providers": {
        "charcoal/cache/service-provider/cache": {}
    }
}
```

## Overview

### Service Provider

#### Parameters

* **cache/available-drivers**: Collection of registered cache drivers that are supported by this system (via [`Stash\DriverList`][stash-drivers]).

#### Services

* **cache/config**: Configuration object for the caching service.  
    See [Pool Configuration](#pool-configuration) for available options.
* **cache/drivers**: Collection of cache driver instances (as a service container) which uses `cache/available-drivers`.  
    These drivers are pre-configured:
  * **file**: [FileSystem](https://www.stashphp.com/Drivers.html#filesystem)
  * **db**: [SQLite](https://www.stashphp.com/Drivers.html#sqlite)
  * **apc**: [APC](https://www.stashphp.com/Drivers.html#apc)
  * **memcache**: [Memcached](https://www.stashphp.com/Drivers.html#memcached)
  * **redis**: [Redis](https://www.stashphp.com/Drivers.html#redis)
  * **memory**: [Ephemeral](https://www.stashphp.com/Drivers.html#ephemeral) (Runtime Only)
  * **noop**: Blackhole (NULL caching driver)
* **cache/builder**: Instance of [`CacheBuilder`][src-builder] that is used to build a cache pool.
* **cache/driver**: Reference to the Stash cache driver used by `cache`. Defaults to "memory".
* **cache**: Main instance of the Stash cache pool which uses `cache/driver` and `cache/config.prefix`.

## Configuration

### Pool Configuration

Each pool comes with a set of default options which can be individually overridden.

| Setting         | Type       | Default    | Description |
|:----------------|:----------:|:----------:|:------------|
| **active**      | `boolean`  | `TRUE`     | Whether to enable or disable the cache service.
| **prefix**      | `string`   | `charcoal` | Name of the main Stash pool.
| **types**       | `string[]` | `memory`   | List of cache drivers to choose from for the main Stash pool. Defaults to "memory".
| **default_ttl** | `integer`  | 1 week     | Default time-to-live (in seconds) for a cached item. Currently, only used by the APC driver (`cache/drivers.apc`).

```php
use Charcoal\Cache\CacheConfig;
use Charcoal\Cache\ServiceProvider\CacheServiceProvider;

$container->register(new CacheServiceProvider());

$container['cache/config'] = new CacheConfig([
    'prefix' => 'foobar',
    'types'  => [ 'apc', 'memcache', 'redis' ],
]);
```

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

### Middleware

The [`CacheMiddleware`][src-middleware] is available for PSR-7 applications that support middleware. The middleware saves the HTTP response body and headers into a [PSR-6 cache pool](psr-6) and returns that cached response if still valid.

If you are using [charcoal/app], you can add the middleware via the application configset:

```json
"middlewares": {
    "charcoal/cache/middleware/cache": {
        "active": true,
        "methods": [ "GET", "HEAD" ]
    }
}
```

Otherwise, with [Slim](https://github.com/slimphp/slim), for example:

```php
use Charcoal\Cache\Middleware\CacheMiddleware;
use Slim\App;
use Stash\Pool;

$app = new App();

// Register middleware
$app->add(new CacheMiddleware([
    'cache'   => new Pool(),
    'methods' => [ 'GET', 'HEAD' ],
]));
```

The middleware comes with a set of default options which can be individually overridden.

| Setting            | Type                     | Default     | Description |
|:-------------------|:------------------------ |:-----------:|:------------|
| **active**         | `boolean`                | `FALSE`     | Whether to enable or disable the middleware ([charcoal/app] only).
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

### Helpers

#### CachePoolAwareTrait

The [`CachePoolAwareTrait`][src-helper] is offered as a convenience to avoid duplicate / boilerplate code. It simply sets and gets an instance of `\Psr\Cache\CacheItemPoolInterface`.

Assign a cache pool with `setCachePool()` and retrieve it with `cachePool()`.  

Both methods are protected; this trait has no public interface.

## Resources

* [Contributing](https://github.com/charcoalphp/.github/blob/main/CONTRIBUTING.md)
* [Report issues](https://github.com/charcoalphp/charcoal/issues) and
  [send pull requests](https://github.com/charcoalphp/charcoal/pulls)
  in the [main Charcoal repository](https://github.com/charcoalphp/charcoal)

[src-middleware]: src/Charcoal/Cache/Middleware/CacheMiddleware.php
[src-provider]:   src/Charcoal/Cache/ServiceProvider/CacheServiceProvider.php
[src-helper]:     src/Charcoal/Cache/CachePoolAwareTrait.php
[src-builder]:    src/Charcoal/Cache/CacheBuilder.php
[src-config]:     src/Charcoal/Cache/CacheConfig.php
[charcoal/app]:   https://github.com/charcoalphp/app
[Stash]:          https://github.com/tedious/Stash
[stash-drivers]:  https://github.com/tedious/Stash/blob/v0.14.2/src/Stash/DriverList.php
[stash-docs]:     https://www.stashphp.com/
[stash-license]:  https://github.com/tedious/Stash/blob/v0.14.2/LICENSE
