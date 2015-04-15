<?php
namespace Charcoal;

use \Slim\Slim as Slim;
use \Charcoal\Config as Config;

use \Psr\Log\LoggerInterface as LoggerInterface;
use \Psr\Log\LogLevel as LogLevel;

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

    static public function init($data=null)
    {
        if(isset($data['config']) && $data['config'] !== null) {
            self::set_config($data['config']);
        }
        else {
            self::$_config = new Config();
        }

        if(isset($data['logger']) && $data['logger'] !== null) {
            self::set_logger($data['logger']);
        }
        else {
            self::init_logger();
        }

        if(isset($data['app']) && $data['app'] !== null) {
            self::set_app($data['app']);
        }
        else {
            self::init_app();
        }



        date_default_timezone_set(self::config()->timezone());
        mb_internal_encoding('UTF-8');
    }

    /**
    * @var mixed $config
    * @throws \InvalidArgumentException if config is not a string, array or Config object
    */
    static public function set_config($config)
    {
        if(self::$_config === null) {
            self::$_config = new Config();
        }
        if(is_string($config)) {
            self::$_config->add_file($config);
        }
        else if(is_array($config)) {
            self::$_config->set_data($config);
        }
        else if($config instanceof Config) {
            self::$_config = $config;
        }
        else {
            throw new \InvalidArgumentException('Config must be a string (filename), array (config data) or Config object');
        }
    }

    static public function config()
    {
        if(self::$_config === null) {
            throw new \Exception('Config has not been set. Call Charcoal::init() first.');
        }
        return self::$_config;
    }

    /**
    * @param LoggerInterface
    */
    static public function set_logger(LoggerInterface $logger)
    {
        self::$_logger = $logger;
    }

    /**
    * @return LoggerInterface
    */
    static public function logger()
    {
        return self::$_logger;
    }

    /**
    * Initialize a default logger.
    * By default, this uses monolog.
    */
    static public function init_logger()
    {
        $logger = new \Monolog\Logger('charcoal');
        $handler = new \Monolog\Handler\StreamHandler('charcoal.debug.log', LogLevel::WARNING);
        $logger->pushHandler($handler);

        self::set_logger($logger);
    }

    static public function set_app(Slim $app)
    {
        self::$_app = $app;
    }

    static public function app()
    {
        return self::$_app;
    }

    /**
    * Initialize a default Slim app
    * This function use the configuration object, so it should have been initialiazed first.
    */
    static public function init_app()
    {
        self::$_app = new Slim(
            [
            'mode'    => self::config()->application_env(),
            'debug'    => self::config()->dev_mode(),
            //'log.writer' => self::logger()
            ]
        );
    }

    /**
    * Rewrite the "array_merge_recursive" function to behave more like standard "array_merge"
    * (overwrite values instead of appending them)
    *
    * From http://www.php.net/manual/en/function.array-merge-recursive.php#104145
    *
    * @param array $array1
    * @param array $array2,...
    *
    * @throws \InvalidArgumentException if there is not at least 2 arguments or any arguments are not array
    * @return array Merged array
    */
    static public function merge()
    {
        $args = func_get_args();
        if(func_num_args() < 2) {
            throw new \InvalidArgumentException('This function takes at least two parameters');
        }

        $array_list = func_get_args();
        $result = [];

        while($array_list) {

            $current = array_shift($array_list);

            // Make sure the argument is an array. @todo: Convert objects to array??
            if(!is_array($current)) {
                throw new \InvalidArgumentException('All parameters must be arrays');
            }
            if(!$current) {
                continue;
            }

            foreach($current as $key => $value) {
                if(is_string($key)) {
                    if(is_array($value) && array_key_exists($key, $result) && is_array($result[$key])) {
                        $result[$key] = call_user_func([__CLASS__, __FUNCTION__], $result[$key], $value);
                    }
                    else {
                        $result[$key] = $value;
                    }
                }
                else {
                    $result[] = $value;
                }
            }
        }

        return $result;
    }
}
