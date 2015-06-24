<?php

namespace Charcoal\Config;

/**
* Config Interface
*/
interface ConfigInterface
{
    /**
    * @param array $data
    * @return ConfigInterface Chainable
    */
    public function set_data($data);

    /**
    * @return array
    */
    public function default_data();

    /**
    * @param string $path
    * @return AbstractConfig (Chainable)
    */
    public function add_file($path);
}
