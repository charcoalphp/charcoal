# Delegated Data Lookup

Delegates allow several objects to share values and act as fallbacks when the current object cannot resolve a given data key.

> 👉 The delegates feature is only implemented by the `Config` class, by default.

**Mixin**

-   `Charcoal\Config\DelegatesAwareInterface`
-   `Charcoal\Config\DelegatesAwareTrait`

In Config objects, _delegate objects_ are regsitered to an internal stack. If a data key cannot be resolved, the Config iterates over each delegate in the stack and stops on
the first match containing a value that is not `NULL`.

```php
use Charcoal\Config\GenericConfig as Config;

$cfg = new Config([
    'driver' => null,
    'host'   => 'localhost',
]);
$delegate = new Config([
    'driver' => 'pdo_mysql',
    'host'   => 'example.com',
    'port'   => 11211,
]);

$cfg->addDelegate($delegate);

echo $cfg['driver']; // "pdo_mysql"
echo $cfg['host']; // "localhost"
echo $cfg['port']; // 11211
```

A delegate can be registered to a Config object via the `addDelegate($delegate)` method, or by direct instantiation with the 2nd parameter:

```php
$cfg = new Config('path/config.json', [ $del1, $del2 ]);
```

> 👉 The order of the delegate stack is important. They are looked in the order they are added, so the first match is returned. Use the `prependDelegate($delegate)` method to add a delegate to the front of the stack (top priority).

Delegates can be registered with:

-   `setDelegates()` — to replace stack with a new collection of delegates.
-   `addDelegate()` — to add a delegate to the end of the stack.
-   `prependDelegate()` — to add a delegate to the beginning of the stack.
