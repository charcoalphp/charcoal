# Key Separator Lookup

It is possible to lookup, retrieve, assign, or merge values in multi-dimensional arrays using _key separators_.

> 👉 The key separator feature is only implemented by the `Config` class, by default.

**Mixin**

-   `Charcoal\Config\SeparatorAwareInterface`
-   `Charcoal\Config\SeparatorAwareTrait`

In Config objects, the default separator is the period character (`.`). The token can be retrieved with the `separator()` method and customized using `setSeparator()` method.

**Example #1: Change key separator**

```php
$cfg = new Charcoal\Config\GenericConfig();
$cfg->setSeparator('/');
```

> The separator must be a single character; an exception will be thrown if any longer.

The mixin can be disabled by assigning an empty string to `setSeparator()`.

**Example #2: Disable key separator**

```php
$cfg = new Charcoal\Config\GenericConfig();
$cfg->setSeparator('');
```

**Example #3: Lookup a nested value**

```php
use Charcoal\Config\GenericConfig as Config;

$cfg = new Config();
$cfg->setSeparator('/');
$cfg->setData([
    'connections' => [
	    'default' => [
	        'driver'      => 'pdo_mysql',
	        'host'        => 'web.someplace.tld',
	        'charset'     => 'utf8mb4',
	        'unix_socket' => '/tmp/mysql.sock',
	    ],
	    'customer' => [
	        'driver'      => 'pdo_mysql',
	        'host'        => 'customer.someplace.tld',
	        'charset'     => 'utf8mb4',
	        'unix_socket' => null,
	    ],
    ]
]);

echo $cfg['connections/default/driver']; // "pdo_mysql"
var_dump($cfg['connections/analytics/driver']); // NULL
```

**Example #4: Assign a nested value**

```php
$cfg['connections/analytics/driver'] = 'pdo_pgsql';
var_dump($cfg['connections/analytics']); // [ 'driver' => 'pdo_pgsql' ]
```
