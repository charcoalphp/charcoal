# Configurable Objects

**Mixin**

-   `Charcoal\Config\ConfigrableInterface`
-   `Charcoal\Config\ConfigurableTrait`

Configurable objects (which could have been called "_Config Aware_") can have an associated Config object that can help define various properties, states, or other.

The trait provides three methods: `config()`, `setConfig()`, and `createConfig()`.

-   `config()` — Retrieve the Config object or the value of a given key.
    
    ```php
    public ConfigurableTrait::config ( void ) : ConfigInterface
    ```
    
    ```php
    public ConfigurableTrait::config ( string $key [, mixed $default = null ] ) : mixed
    ```

-   `setConfig()` — Assign the given instance of `ConfigInterface `. Otherwise, create a new Config object with the given associative array or the config file to import.
    
    ```php
    public ConfigurableTrait::setConfig ( ConfigInterface $config ) : self
    ```
    
    ```php
    public ConfigurableTrait::setConfig ( mixed $data ) : self
    ```

-   `createConfig()` — Create a new Config object with. Optionally, merge the data from the given instance of `ConfigInterface `, the associative array, or from the config file to import.
    
    ```php
    protected ConfigurableTrait::createConfig ( [ mixed $data = null ] ) : ConfigInterface
    ```

**Example #1: Implementation of the mixin**

```php
namespace Acme;

use Acme\Config;
use Charcoal\Config\ConfigurableInterface;
use Charcoal\Config\ConfigurableTrait;

class App implements ConfigurableInterface
{
    use ConfigurableTrait;

	/**
	 * @param  mixed $data Initial data; a filepath, an array, or another Config object.
	 * @return Config
	 */
    public function createConfig($data = null)
    {
        $cfg = new Config($data);
        if ($data !== null) {
            $cfg->merge($data);
        }
        return $cfg;
    }
}
```

The class above could be used as such:

```php
use Acme\App;

$foo = new App();
$foo->setConfig([
    'foo' => [
        'baz' => 42,
        'qux' => null,
    ]
]);

$cfg = $foo->config();
echo $cfg['foo.baz']; // Returns 42
echo $foo->config('foo.baz'); // Also, 42
echo $foo->config('foo.qux', -1); // Returns -1
```
