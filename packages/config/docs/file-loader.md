# Data File Loader

Array data can be parsed from files depending on the file extension. This is useful for accessing configuration data within application code.

> 👉 The file loading feature is only implemented by the `Config` class, by default.

#### Mixin

-   [`Charcoal\Config\FileAwareInterface`](src/Charcoal/Config/FileAwareInterface.php)
-   [`Charcoal\Config\FileAwareTrait`](src/Charcoal/Config/FileAwareTrait.php)

#### Supported File Formats

-   [INI](#ini-configuration-files)
-   [JSON](#json-configuration-files)
-   [PHP](#php-configuration-files)
-   [YAML](#yaml-configuration-files)



## Loading Files

> **Data Structure**
> 
> Note that the file contents must be parsable into an array, which may be multi-dimensional.

A file can be imported into a variable via the `loadFile($path) : array` method.

```php
$data = $cfg->loadFile('config.php');
```

The file will be parsed and, if its an array, will be returned. If its NOT an array, an empty array will be returned instead.



## Config Object

A configuration file can be imported into a Config object via the `addFile($path) : self` method, or by direct instantiation:

```php
use Charcoal\Config\GenericConfig as Config;

$cfg = new Config('config.json');
$cfg->addFile('config.yml');
```

The file will be parsed and, if its an array, will be merged into the container.



## INI Configuration Files

For the INI format, the Config uses the [`parse_ini_file()`](https://php.net/parse_ini_file) PHP function.

```ini
host = localhost
port = 11211
memory = false

database.charset = utf8mb4
database.drivers[] = pdo_mysql
database.drivers[] = pdo_pgsql
database.drivers[] = pdo_sqlite

[database]
name = mydb
user = myname
pass = secret
```

The file can be imported like so:

```php
use Charcoal\Config\GenericConfig as Config;

$cfg = new Config('path/to/config.ini');

echo $cfg['host']; // "localhost"
echo $cfg['database']['name']; // "mydb"
var_export($cfg['database']['drivers']); // [ "pdo_mysql", "pdo_pgsql", "pdo_sqlite" ]
```



## JSON Configuration Files

For the JSON format, the Config uses the [`file_get_contents()`](https://php.net/file_get_contents) and [`json_decode()`](https://php.net/json_decode) PHP functions.

```json
{
    "host": "localhost",
    "port": 11211,
    "memory": false,
    "drivers": [
        "pdo_mysql",
        "pdo_pgsql",
        "pdo_sqlite"
    ],
    "database": {
        "name": "mydb",
        "user": "myname",
        "pass": "secret"
    }
}
```

The file can be imported like so:

```php
use Charcoal\Config\GenericConfig as Config;

$cfg = new Config('path/to/config.json');

echo $cfg['host']; // "localhost"
echo $cfg['database']['name']; // "mydb"
var_export($cfg['database']['drivers']); // [ "pdo_mysql", "pdo_pgsql", "pdo_sqlite" ]
```



## PHP Configuration Files

For PHP-based files, the Config uses the `include` statement. The included file inherits the scope (`$this`) of the target Config object.

The PHP file can either return its data or, using the scope, manipulate the Config object directly.

**Example #1: Return data to Config**

```php
// config.php
return [
    'host'     => 'localhost',
    'port'     => 11211,
    'memory'   => false,
    'database' => [
        'name' => 'mydb',
        'user' => 'myname',
        'pass' => 'secret',
    ],
];
```

**Example #2: Mutate Config scope**

```php
// drivers.php
$this['database']['drivers'] = [
    'pdo_mysql',
    'pdo_pgsql',
    'pdo_sqlite',
];
```

Both approaches can be imported like so:

```php
use Charcoal\Config\GenericConfig as Config;

$cfg = new Config();
$cfg->addFile('path/to/config.php');
$cfg->addFile('path/to/drivers.php');

echo $cfg['host']; // "localhost"
echo $cfg['database']['name']; // "mydb"
var_export($cfg['database']['drivers']); // [ "pdo_mysql", "pdo_pgsql", "pdo_sqlite" ]
```

Because the included file's scope references the Config instance, it is possible to include additional files within:

**Example #3: Import files from Config scope**

```php
$this->addFile('path/to/config.json');
```

In Charcoal, this approach is used to load environment-bound configuration files and seperate an application's settings into different topics.



## YAML Configuration Files

For the YAML format, the Config requires the [Symfony YAML component][symfony/yaml].

> ```shell
> $ composer require symfony/yaml
> ```

The Config object supports two different extensions: `.yml` or `.yaml`.

```yaml
host: localhost
port: 11211
memory: false
drivers:
- pdo_mysql
- pdo_pgsql
- pdo_sqlite
database:
    name: mydb
    user: myname
    pass: secret
```

The file can be imported like so:

```php
use Charcoal\Config\GenericConfig as Config;

$cfg = new Config('path/to/config.yml');

echo $cfg['host']; // "localhost"
echo $cfg['database']['name']; // "mydb"
var_export($cfg['database']['drivers']); // [ "pdo_mysql", "pdo_pgsql", "pdo_sqlite" ]
```

[symfony/yaml]: https://packagist.org/packages/symfony/yaml
