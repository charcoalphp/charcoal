<?php

namespace Charcoal\Config;

/**
* Config Interface
*/
interface ConfigInterface
{

    /**
    * @param string $separator
    * @return ConfigInterface Chainable
    */
    public function set_separator($separator);

    /**
    * @return string
    */
    public function separator();


    /**
    * @param array $data
    * @return ConfigInterface Chainable
    */
    public function set_data(array $data);

    /**
    * @return array
    */
    public function default_data();

    /**
    * @param string $key
    * @return mixed
    */
    public function get($key);

    /**
    * @param string $key
    * @param mixed $val
    * @return ConfigInterface Chainable
    */
    public function set($key, $val);



    /**
    * @param string $path
    * @return AbstractConfig (Chainable)
    */
    public function add_file($path);
}
