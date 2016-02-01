<?php

namespace Charcoal\Config;

// Dependencies from `PHP`
use \ArrayAccess;
use \ArrayIterator;
use \Exception;
use \InvalidArgumentException;
use \IteratorAggregate;
use \Traversable;

// Dependencies from `container-interop/container-interop`
use Interop\Container\ContainerInterface;

// Local namespace dependencies
use \Charcoal\Config\ConfigInterface;

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
    const DEFAULT_SEPARATOR = '.';

    /**
     * Default separator for config is "."
     * @var string $separator
     */
    protected $separator = self::DEFAULT_SEPARATOR;

    /**
     * Create the configuration.
     *
     * @param mixed $data Optional default data. Either a filename, an array, or a Config object.
     * @param ConfigInterface[] $delegates An array of delegates (config) to set.
     * @throws InvalidArgumentException If $data is invalid.
     */
    final public function __construct($data = null, array $delegates = null)
    {
        // Always set the default data first.
        $this->merge($this->defaults());

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
                'Data must be an array, a file string or a ConfigInterface object.'
            );
        }
    }

    /**
     * Config gives public access to its constructor.
     *
     * @return string
     */
    public function separator()
    {
        return $this->separator;
    }

    /**
     * Add a configuration file. The file type is determined by its extension.
     *
     * Supported file types are `ini`, `json`, `php`
     *
     * @param string $filename A supported configuration file.
     * @throws InvalidArgumentException If the file is invalid.
     * @return AbstractConfig (Chainable)
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
     * @param string $filename A supported configuration file.
     * @throws InvalidArgumentException If the filename is invalid.
     * @return mixed The file content.
     */
    public function loadFile($filename)
    {
        if (!is_string($filename)) {
            throw new InvalidArgumentException(
                'Configuration file must be a string.'
            );
        }
        if (!file_exists($filename)) {
            throw new InvalidArgumentException(
                sprintf('Configuration file "%s" does not exist', $filename)
            );
        }

        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        if ($ext == 'php') {
            return $this->loadPhpFile($filename);
        } elseif ($ext == 'json') {
            return $this->loadJsonFile($filename);
        } elseif ($ext == 'ini') {
            return $this->loadIniFile($filename);
        } else {
            throw new InvalidArgumentException(
                'Only JSON, INI and PHP files are accepted as a Configuration file.'
            );
        }
    }

     /**
      * For each key, calls `set()`, which calls `offsetSet()`  (from ArrayAccess).
      *
      * The provided `$data` can be a simple array or an object which implements `Traversable`
      * (such as a `ConfigInterface` instance).
      *
      * @param array|Traversable $data The data to set.
      * @return AbstractConfig Chainable
      * @see self::set()
      * @see self::offsetSet()
      */
    public function merge($data)
    {
        foreach ($data as $k => $v) {
                $this->set($k, $v);
        }
        return $this;
    }

    /**
     * A stub for when the default data is empty.
     *
     * Make sure to reimplement in children ConfigInterface classes if any default data should be set.
     *
     * @see ConfigInterface::defaults()
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
     * @param string $filename A INI configuration file.
     * @throws InvalidArgumentException If the file or invalid.
     * @return AbstractConfig Chainable
     */
    private function loadIniFile($filename)
    {
        $config = parse_ini_file($filename, true);
        if ($config === false) {
            throw new InvalidArgumentException(
                sprintf('Ini file "%s" is empty or invalid.')
            );
        }
        return $config;
    }

    /**
     * Add a `.json` file to the configuration.
     *
     * @param string $filename A JSON configuration file.
     * @throws InvalidArgumentException If the file or invalid.
     * @return AbstractConfig Chainable
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
     * @param string $filename A PHP configuration file.
     * @throws InvalidArgumentException If the file or invalid.
     * @return AbstractConfig Chainable
     */
    private function loadPhpFile($filename)
    {
        // `$this` is bound to the current configuration object (Current `$this`)
        $config = include $filename;
        return $config;
    }
}
