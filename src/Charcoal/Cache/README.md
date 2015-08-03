Charcoal Cache
==============

This namespace (part of the `charcoal-core` package) provides a unified API forvarious cache backends.

# Usage

```php
$cache = CacheFactory::instance()->get('memcache');

// Store data in the cache
$cache->store($key, $data, $ttl);
// Check if key exists in cache
$cache->exists($key);
// Fetch data from cache key
$cache->fetch($key);
// Fetch multiple data from cache keys
$cache->multifetch($keys);
// Delete a key in the cacge
public function delete($key);
// Completely clear the cache.
public function clear();
```

# Available backends

There are currently 3 available cache backends:
- `apc`
  - The APC cache must be enabled in PHP.
- `memcache`
  - The `memcache` PHP extension must be enabled in PHP.
  - Do not confuse with the similar `memcached` extension. Although alike, they are not compatible.
- `noop`
  - A _null_ cache is provided to use the caching system without an actual bacekend.

> ⚠ The APC driver should not be used in PHP > 5.5.

## How to select a driver:

Directly:
```
$cache = MemcacheCache::instance();
```
_Other base classes are `ApcCache` and `NoopCache`_

With the CacheFactory:
```php
use \Charcoal\Cache\CacheFactory;
$cache = CacheFactory::instance()->get('memcache');
```
_Other cache idents are `apc` and `noop`_

# Usage within Charcoal



# Development

This package is distributed as a namespace inside `charcoal-core`. The development process is therefore the same as `charcoal-core`'s.

## TODOs

- Write a redis backend
- Custom Exceptions

