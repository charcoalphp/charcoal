<?php

namespace Charcoal\Config;

// Dependencies from `PHP`
use \ArrayAccess;
use \Exception;
use \InvalidArgumentException;

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
abstract class AbstractConfig implements
    ConfigInterface,
    ContainerInterface,
    ArrayAccess
{
    const DEFAULT_SEPARATOR = '/';

    /**
     * Delimiter for accessing nested options.
     *
     * @var string $separator
     */
    private $separator = self::DEFAULT_SEPARATOR;

    /**
     * Delegates act as fallbacks when the current object
     * doesn't have a requested option.
     *
     * @var ConfigInterface[] $delegates
     */
    private $delegates = [];

    /**
     * Create the configuration
     *
     * @param array|string|null $data Optional default data, as `[$key => $val]` array.
     * @param ConfigInterface[] $delegates An array of delegates (config) to set.
     * @throws InvalidArgumentException If $data is invalid.
     * @todo Implement data migration from a passed ConfigInterface.
     */
    public function __construct($data = null, array $delegates = null)
    {
        if (is_string($data)) {
            $this->set_data($this->default_data());
            $this->add_file($data);
        } elseif (is_array($data)) {
             $data = array_merge($this->default_data(), $data);
             $this->set_data($data);
        } elseif ($data === null) {
            $data = $this->default_data();
            $this->set_data($data);
        } else {
            throw new InvalidArgumentException(
                'Data must be an array, a file string or a ConfigInterface object.'
            );
        }

        if (isset($delegates)) {
            $this->set_delegates($delegates);
        }
    }

    /**
     * @param ConfigInterface[] $delegates The array of delegates (config) to set.
     * @return ConfigInterface Chainable
     */
    public function set_delegates(array $delegates)
    {
        $this->delegates = [];
        foreach ($delegates as $delegate) {
            $this->add_delegate($delegate);
        }
        return $this;
    }

    /**
     * @param ConfigInterface[] $delegates A delegate (config) instance.
     * @return ConfigInterface Chainable
     */
    public function add_delegate(ConfigInterface $delegate)
    {
        $this->delegates[] = $delegate;
        return $this;
    }

    /**
     * @param ConfigInterface[] $delegates A delegate (config) instance.
     * @return ConfigInterface Chainable
     */
    public function prepend_delegate(ConfigInterface $delegate)
    {
        array_unshift($this->delegates, $delegate);
        return $this;
    }

    /**
     * @param string $separator A single-character to delimite nested options.
     * @throws InvalidArgumentException If $separator is invalid.
     * @return AbstractConfig Chainable
     */
    public function set_separator($separator)
    {
        if (!is_string($separator)) {
            throw new InvalidArgumentException(
                'Separator needs to be a string.'
            );
        }
        if (strlen($separator) > 1) {
            throw new InvalidArgumentException(
                'Separator needs to be only one-character.'
            );
        }
        $this->separator = $separator;
        return $this;
    }

    /**
     * @return string
     */
    public function separator()
    {
        return $this->separator;
    }

    /**
     * For each key, calls `set()`, which calls `offsetSet()`  (from ArrayAccess)
     *
     * @param array $data The data to set.
     * @return AbstractConfig Chainable
     * @see self::set()
     * @see self::offsetSet()
     */
    public function set_data(array $data)
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
     * @see ConfigInterface::default_data()
     * @return array
     */
    public function default_data()
    {
        return [];
    }

    /**
     * Find an entry of the configuration by its key and retrieve it.
     *
     * @see self::offsetGet()
     * @param string $key The key of the configuration item to look for.
     * @return mixed
     */
    public function get($key)
    {
        return $this[$key];
    }

    /**
     * Assign a value to the specified key of the configuration.
     *
     * @see self::offsetSet()
     * @param string $key The key to assign $value to.
     * @param mixed $value Value to assign to $key.
     * @return AbstractConfig Chainable
     */
    public function set($key, $value)
    {
        $this[$key] = $value;
        return $this;
    }

    /**
     * Determine if a configuration key exists.
     *
     * @see self::offsetExists()
     * @param string $key The key of the configuration item to look for.
     * @return boolean
     */
    public function has($key)
    {
        return isset($this[$key]);
    }

    /**
     * @param string $key The key of the configuration item to fetch.
     * @return mixed The item, if found, or null.
     */
    private function get_in_delegates($key)
    {
        foreach ($this->delegates as $delegate) {
            if ($delegate->has($key)) {
                return $delegate->get($key);
            }
        }

        return null;
    }

    /**
     * @param string $key The key of the configuration item to check.
     */
    private function has_in_delegates($key)
    {
        foreach ($this->delegates as $delegate) {
            if ($delegate->has($key)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determine if a configuration key exists.
     *
     * @see ArrayAccess::offsetExists()
     * @param string $key The key of the configuration item to look for.
     * @throws InvalidArgumentException If the key argument is not a string or is a "numeric" value.
     * @return boolean
     */
    public function offsetExists($key)
    {
        if (is_numeric($key)) {
            throw new InvalidArgumentException(
                'Config array access only supports non-numeric keys.'
            );
        }

        if (strstr($key, $this->separator())) {
            return $this->has_with_separator($key);
        }

        if (is_callable([$this, $key])) {
            $value = $this->{$key}();
        } else {
            if (!isset($this->{$key})) {
                return $this->has_in_delegates($key);
            }
            $value = $this->{$key};
        }
        return ($value !== null);
    }

    /**
     * Find an entry of the configuration by its key and retrieve it.
     *
     * @see ArrayAccess::offsetGet()
     * @param string $key The key of the configuration item to look for.
     * @throws InvalidArgumentException If the key argument is not a string or is a "numeric" value.
     * @return mixed The value (or null)
     */
    public function offsetGet($key)
    {
        if (is_numeric($key)) {
            throw new InvalidArgumentException(
                'Config array access only supports non-numeric keys.'
            );
        }
        if (strstr($key, $this->separator())) {
            return $this->get_with_separator($key);
        }
        $getter = $key;
        if (is_callable([$this, $getter])) {
            return $this->{$getter}();
        } else {
            if (isset($this->{$key})) {
                return $this->{$key};
            } else {
                return $this->get_in_delegates($key);
            }
        }
    }

    /**
     * Assign a value to the specified key of the configuration.
     *
     * @see ArrayAccess::offsetSet()
     * @param string $key The key to assign $value to.
     * @param mixed $value Value to assign to $key.
     * @throws InvalidArgumentException If the key argument is not a string or is a "numeric" value.
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (is_numeric($key)) {
            throw new InvalidArgumentException(
                'Config array access only supports non-numeric keys.'
            );
        }

        if (strstr($key, $this->separator())) {
            return $this->set_with_separator($key, $value);
        } else {
            $setter = 'set_'.$key;
            if (is_callable([$this, $setter])) {
                $this->{$setter}($value);
            } else {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * @param string $key The key of the configuration item to look for.
     * @return mixed The value (or null)
     */
    private function get_with_separator($key)
    {
        $arr = $this;
        $split_keys = explode($this->separator(), $key);
        foreach ($split_keys as $k) {
            if (!isset($arr[$k])) {
                return $this->get_in_delegates($key);
            }
            if (!is_array($arr[$k]) && ($arr[$k] instanceof ArrayAccess)) {
                return $arr[$k];
            }
            $arr = $arr[$k];
        }
        return $arr;
    }

    /**
     * @param string $key The key of the configuration item to look for.
     * @return boolean
     */
    private function has_with_separator($key)
    {
        $arr = $this;
        $split_keys = explode($this->separator(), $key);
        foreach ($split_keys as $k) {
            if (!isset($arr[$k])) {
                return $this->has_in_delegates($key);
            }
            if (!is_array($arr[$k]) && ($arr[$k] instanceof ArrayAccess)) {
                return true;
            }
            $arr = $arr[$k];
        }
        return true;
    }

    /**
     * @param string $key The key to assign $value to.
     * @param mixed $value Value to assign to $key.
     * @throws Exception If a value already exists and is scalar (can not be merged).
     * @return void
     */
    private function set_with_separator($key, $value)
    {
        $split_keys = explode($this->separator(), $key);
        $first = array_shift($split_keys);

        $lvl = 1;
        $num = count($split_keys);

        $source = $this[$first];
        $ref = &$result;

        foreach ($split_keys as $p) {

            if ($lvl == $num) {
                $ref[$p] = $value;
            } else {
                if (!isset($source[$p])) {
                    $ref[$p] = [];
                } else {
                    if (is_array($source[$p]) || ($source[$p] instanceof ArrayAccess)) {
                        $ref[$p] = $source[$p];

                    } else {
                        throw new Exception(
                            sprintf('Can not set recursively with separator.')
                        );
                    }

                }
            }

            $ref = &$ref[$p];
            $lvl++;
        }

        // Merge, if necessary.
        if ($this->has($first)) {
            $result = ($this[$first] + $result);
        }

        $this[$first] = $result;
    }

    /**
     * ArrayAccess > offsetUnset()
     *
     * @param string $key The key of the configuration item to remove.
     * @throws InvalidArgumentException If the key argument is not a string or is a "numeric" value.
     * @return void
     */
    public function offsetUnset($key)
    {
        if (is_numeric($key)) {
            throw new InvalidArgumentException(
                'Config array access only supports non-numeric keys.'
            );
        }
        $this[$key] = null;
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
    public function add_file($filename)
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
            return $this->add_php_file($filename);
        } elseif ($ext == 'json') {
            return $this->add_json_file($filename);
        } elseif ($ext == 'ini') {
            return $this->add_ini_file($filename);
        } else {
            throw new InvalidArgumentException(
                'Only JSON, INI and PHP files are accepted as a Configuration file.'
            );
        }
    }

    /**
     * Add a `.ini` file to the configuration
     *
     * @param string $filename A INI configuration file.
     * @throws InvalidArgumentException If the file or invalid.
     * @return AbstractConfig Chainable
     */
    private function add_ini_file($filename)
    {
        $config = parse_ini_file($filename, true);
        if ($config === false) {
            throw new InvalidArgumentException(
                sprintf('Ini file "%s" is empty or invalid.')
            );
        }
        $this->set_data($config);
        return $this;
    }

    /**
     * Add a `.json` file to the configuration
     *
     * @param string $filename A JSON configuration file.
     * @throws InvalidArgumentException If the file or invalid.
     * @return AbstractConfig Chainable
     */
    private function add_json_file($filename)
    {
        $file_content = file_get_contents($filename);
        $config = json_decode($file_content, true);
        $err_code = json_last_error();
        if ($err_code == JSON_ERROR_NONE) {
            $this->set_data($config);
            return $this;
        }
        // Handle JSON error
        switch ($err_code) {
            case JSON_ERROR_NONE:
                break;
            case JSON_ERROR_DEPTH:
                $err_msg = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $err_msg = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $err_msg = 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $err_msg = 'Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $err_msg = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $err_msg = 'Unknown error';
                break;
        }

        throw new InvalidArgumentException(
            sprintf('JSON file "%s" could not be parsed: "%s"', $filename, $err_msg)
        );

    }

    /**
     * Add a PHP file to the configuration
     *
     * @param string $filename A PHP configuration file.
     * @throws InvalidArgumentException If the file or invalid.
     * @return AbstractConfig Chainable
     */
    private function add_php_file($filename)
    {
        // `$this` is bound to the current configuration object (Current `$this`)
        $config = include $filename;
        if (is_array($config)) {
            $this->set_data($config);
        }
        return $this;
    }
}
