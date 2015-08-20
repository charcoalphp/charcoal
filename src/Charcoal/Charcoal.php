<?php

namespace Charcoal;

// Dependencies from `PHP`
use \Exception;
use \InvalidArgumentException;

// 3rd-party libraries dependencies
use \Slim\App as Slim;
use \Psr\Log\LoggerInterface;
use \Psr\Log\LogLevel;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\CharcoalConfig;

/**
* Main Charcoal container.
*
* The Charcoal container has:
* - `config`
*   - The root configuration object.
*   - Should implement `\Charcoal\ConfigInterface`
* - `logger`
*   - The PSR-3 compatible logger
*   - Should implement `\Psr\Log\LoggerInterface`
* - `app`
*   - The Slim application
*   - An instance of `\Slim\App`
*/
class Charcoal
{
    /**
    * @var Config $_config
    */
    static private $_config = null;

    /**
    * @var LoggerInterface $_logger
    */
    static private $_logger = null;

    /**
    * @var Slim $_app
    */
    static private $_app = null;

    /**
    * @param array|null $data
    * @return void
    */
    public static function init($data = null)
    {
        // Important: set config first. This affects later behavior
        if (isset($data['config']) && $data['config'] !== null) {
            self::set_config($data['config']);
        }

        if (isset($data['logger']) && $data['logger'] !== null) {
            self::set_logger($data['logger']);
        }

        if (isset($data['app']) && $data['app'] !== null) {
            self::set_app($data['app']);
        }

       // date_default_timezone_set(self::config()->get('timezone'));
        //mb_internal_encoding('UTF-8');
    }

    /**
    * @param mixed $config
    * @throws InvalidArgumentException if config is not a string, array or Config object
    * @return void
    */
    public static function set_config($config)
    {
        if ($config instanceof CharcoalConfig) {
            self::$_config = $config;
        } else {
            throw new InvalidArgumentException(
                'Config must be a string (filename), an array (config data), or a Config object.'
            );
        }
    }

    /**
    * @param mixed $opt
    * @throws Exception
    * @return Config
    */
    public static function config($opt = null)
    {
        if (self::$_config === null) {
            throw new Exception(
                'Config has not been set. Call Charcoal::init() first.'
            );
        }
        if ($opt !== null) {
            return self::$_config[$opt];
        }
        return self::$_config;
    }

    /**
    * @param LoggerInterface $logger
    * @return void
    */
    public static function set_logger(LoggerInterface $logger)
    {
        self::$_logger = $logger;
    }

    /**
    * @return LoggerInterface
    */
    public static function logger()
    {
        return self::$_logger;
    }

    /**
    * @param Slim $app
    * @return void
    */
    public static function set_app(Slim $app)
    {
        self::$_app = $app;
    }

    /**
    * @return Slim
    */
    public static function app()
    {
        return self::$_app;
    }

    /**
    * Rewrite the "array_merge_recursive" function to behave more like standard "array_merge"
    * (overwrite values instead of appending them)
    *
    * From http:// www.php.net/manual/en/function.array-merge-recursive.php#104145
    *
    * @param array $array1 Initial array to merge.
    * @param array $... Variable list of arrays to merge.
    * @throws InvalidArgumentException If there isn't at least 2 arguments or any arguments are not an array
    * @return array Merged array
    */
    public static function merge()
    {
        $args = func_get_args();
        if (func_num_args() < 2) {
            throw new InvalidArgumentException('This function takes at least two parameters.');
        }

        $array_list = func_get_args();
        $result = [];

        while ($array_list) {
            $current = array_shift($array_list);

            /** @todo Convert objects to array? */
            if (!is_array($current)) {
                throw new InvalidArgumentException('All parameters must be arrays.');
            }
            if (!$current) {
                continue;
            }

            foreach ($current as $key => $value) {
                if (is_string($key)) {
                    if (is_array($value) && array_key_exists($key, $result) && is_array($result[$key])) {
                        $result[$key] = call_user_func([__CLASS__, __FUNCTION__], $result[$key], $value);
                    } else {
                        $result[$key] = $value;
                    }
                } else {
                    $result[] = $value;
                }
            }
        }

        return $result;
    }
}
