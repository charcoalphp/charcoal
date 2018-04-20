<?php

namespace Charcoal\Config;

// Dependencies from `PHP`
use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

// Dependencies from `symfony/yaml`
use Symfony\Component\Yaml\Parser as YamlParser;

// Dependencies from `container-interop/container-interop`
use Interop\Container\ContainerInterface;

/**
 * Configuration container / registry.
 *
 * An abstract class that fulfills the full ConfigInterface.
 *
 * This class also implements the `ArrayAccess` interface, so each member can be accessed with `[]`.
 */
abstract class AbstractConfig extends AbstractEntity implements
    ConfigInterface,
    ContainerInterface,
    IteratorAggregate
{
    use DelegatesAwareTrait;
    use SeparatorAwareTrait;

    const DEFAULT_SEPARATOR = '.';

    /**
     * Create the configuration.
     *
     * @param  mixed             $data      Optional default data. Either a filename, an array, or a Config object.
     * @param  ConfigInterface[] $delegates An array of delegates (config) to set.
     * @throws InvalidArgumentException If $data is invalid.
     */
    final public function __construct($data = null, array $delegates = null)
    {
        $this->setSeparator(self::DEFAULT_SEPARATOR);
        // Always set the default data first.
        $this->setData($this->defaults());

        // Set the delegates, if necessary.
        if (isset($delegates)) {
            $this->setDelegates($delegates);
        }

        if ($data === null) {
            return;
        }

        if (is_string($data)) {
            // Treat the parameter as a filename
            $this->addFile($data);
        } elseif (is_array($data)) {
            $this->merge($data);
        } elseif ($data instanceof ConfigInterface) {
            $this->merge($data);
        } else {
            throw new InvalidArgumentException(
                'Data must be an array, a file string or a ConfigInterface object'
            );
        }
    }

    /**
     * Determine if a configuration key exists.
     *
     * @see    ArrayAccess::offsetExists()
     * @param  string $key The key of the configuration item to look for.
     * @throws InvalidArgumentException If the key argument is not a string or is a "numeric" value.
     * @return boolean
     */
    public function offsetExists($key)
    {
        if (is_numeric($key)) {
            throw new InvalidArgumentException(
                'Entity array access only supports non-numeric keys'
            );
        }

        if ($this->separator && strstr($key, $this->separator)) {
            return $this->hasWithSeparator($key);
        }

        $key = $this->camelize($key);
        if (is_callable([$this, $key])) {
            $value = $this->{$key}();
        } else {
            if (!isset($this->{$key})) {
                return $this->hasInDelegates($key);
            }
            $value = $this->{$key};
        }
        return ($value !== null);
    }

    /**
     * Find an entry of the configuration by its key and retrieve it.
     *
     * @see    ArrayAccess::offsetGet()
     * @param  string $key The key of the configuration item to look for.
     * @throws InvalidArgumentException If the key argument is not a string or is a "numeric" value.
     * @return mixed The value (or null)
     */
    public function offsetGet($key)
    {
        if (is_numeric($key)) {
            throw new InvalidArgumentException(
                'Entity array access only supports non-numeric keys'
            );
        }
        if ($this->separator && strstr($key, $this->separator)) {
            return $this->getWithSeparator($key);
        }
        $key = $this->camelize($key);

        if (is_callable([$this, $key])) {
            return $this->{$key}();
        } else {
            if (isset($this->{$key})) {
                return $this    ->{$key};
            } else {
                return $this->getInDelegates($key);
            }
        }
    }

    /**
     * Assign a value to the specified key of the configuration.
     *
     * Set the value either by:
     * - a setter method (`set_{$key}()`)
     * - setting (or overriding)
     *
     * @see    ArrayAccess::offsetSet()
     * @param  string $key   The key to assign $value to.
     * @param  mixed  $value Value to assign to $key.
     * @throws InvalidArgumentException If the key argument is not a string or is a "numeric" value.
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (is_numeric($key)) {
            throw new InvalidArgumentException(
                'Entity array access only supports non-numeric keys'
            );
        }

        if ($this->separator && strstr($key, $this->separator)) {
            $this->setWithSeparator($key, $value);
        } else {
            $key = $this->camelize($key);
            $setter = 'set'.ucfirst($key);

            // Case: url.com?_=something
            if ($setter === 'set') {
                return;
            }

            if (is_callable([$this, $setter])) {
                $this->{$setter}($value);
            } else {
                $this->{$key} = $value;
            }
            $this->keys[$key] = true;
        }
    }


    /**
     * Add a configuration file. The file type is determined by its extension.
     *
     * Supported file types are `ini`, `json`, `php`
     *
     * @param  string $filename A supported configuration file.
     * @throws InvalidArgumentException If the file is invalid.
     * @return self
     */
    public function addFile($filename)
    {
        $content = $this->loadFile($filename);
        if (is_array($content)) {
            $this->merge($content);
        }
        return $this;
    }

    /**
     * Load a configuration file. The file type is determined by its extension.
     *
     * Supported file types are `ini`, `json`, `php`
     *
     * @param  string $filename A supported configuration file.
     * @throws InvalidArgumentException If the filename is invalid.
     * @return mixed
     */
    public function loadFile($filename)
    {
        if (!is_string($filename)) {
            throw new InvalidArgumentException(
                'Configuration file must be a string'
            );
        }
        if (!file_exists($filename)) {
            throw new InvalidArgumentException(
                sprintf('Configuration file "%s" does not exist', $filename)
            );
        }

        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        if ($ext === 'php') {
            return $this->loadPhpFile($filename);
        } elseif ($ext === 'json') {
            return $this->loadJsonFile($filename);
        } elseif ($ext === 'ini') {
            return $this->loadIniFile($filename);
        } elseif ($ext === 'yml' || $ext === 'yaml') {
            return $this->loadYamlFile($filename);
        } else {
            $validConfigExts = [ 'php', 'json', 'yml', 'ini' ];
            throw new InvalidArgumentException(sprintf(
                'Unsupported configuration file; must be one of "%s", received "%s"',
                implode('","', $validConfigExts),
                $ext
            ));
        }
    }

    /**
     * For each key, calls `set()`, which calls `offsetSet()`  (from ArrayAccess).
     *
     * The provided `$data` can be a simple array or an object which implements `Traversable`
     * (such as a `ConfigInterface` instance).
     *
     * @see    self::offsetSet()
     * @param  array|Traversable|ConfigInterface $data The data to set.
     * @return self
     */
    public function merge($data)
    {
        foreach ($data as $k => $v) {
            if (is_array($v) && isset($this[$k]) && is_array($this[$k])) {
                $v = array_replace_recursive($this[$k], $v);
            }
            $this[$k] = $v;
        }
        return $this;
    }


    /**
     * A stub for when the default data is empty.
     *
     * Make sure to reimplement in children ConfigInterface classes if any default data should be set.
     *
     * @see    ConfigInterface::defaults()
     * @return array
     */
    public function defaults()
    {
        return [];
    }

    /**
     * IteratorAggregate > getIterator()
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data());
    }

    /**
     * Add a `.ini` file to the configuration.
     *
     * @param  string $filename A INI configuration file.
     * @throws InvalidArgumentException If the file or invalid.
     * @return mixed
     */
    private function loadIniFile($filename)
    {
        $config = parse_ini_file($filename, true);
        if ($config === false) {
            throw new InvalidArgumentException(
                sprintf('Ini file "%s" is empty or invalid', $filename)
            );
        }
        return $config;
    }

    /**
     * Add a `.json` file to the configuration.
     *
     * @param  string $filename A JSON configuration file.
     * @throws InvalidArgumentException If the file or invalid.
     * @return mixed
     */
    private function loadJsonFile($filename)
    {
        $fileContent = file_get_contents($filename);
        $config = json_decode($fileContent, true);
        $errCode = json_last_error();
        if ($errCode == JSON_ERROR_NONE) {
            return $config;
        }

        // Handle JSON error
        $errMsg = '';
        switch ($errCode) {
            case JSON_ERROR_NONE:
                break;
            case JSON_ERROR_DEPTH:
                $errMsg = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $errMsg = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $errMsg = 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $errMsg = 'Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $errMsg = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $errMsg = 'Unknown error';
                break;
        }

        throw new InvalidArgumentException(
            sprintf('JSON file "%s" could not be parsed: "%s"', $filename, $errMsg)
        );
    }

    /**
     * Add a PHP file to the configuration
     *
     * @param  string $filename A PHP configuration file.
     * @return mixed
     */
    private function loadPhpFile($filename)
    {
        // `$this` is bound to the current configuration object (Current `$this`)
        $config = include $filename;
        return $config;
    }

    /**
     * Add a YAML file to the configuration
     *
     * @param  string $filename A YAML configuration file.
     * @throws InvalidArgumentException If the YAML file can not correctly be parsed into an array.
     * @return mixed
     */
    private function loadYamlFile($filename)
    {
        $parser = new YamlParser();
        $fileContent = file_get_contents($filename);
        $config = $parser->parse($fileContent);
        if (!is_array($config)) {
            throw new InvalidArgumentException(
                sprintf('YAML file "%s" could not be parsed (invalid yaml)', $filename)
            );
        }
        return $config;
    }
}
