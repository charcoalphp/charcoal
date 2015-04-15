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
}
