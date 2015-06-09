<?php

namespace Charcoal\Source;

use \Charcoal\Config\ConfigurableInterface as ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait as ConfigurableTrait;

use \Charcoal\Source\SourceConfig as SourceConfig;
use \Charcoal\Source\SourceInterface as SourceInterface;

abstract class AbstractSource implements
    SourceInterface,
    ConfigurableInterface
{
    use ConfigurableTrait;

    /**
    * ConfigurableTrait > create_config()
    */
    public function create_config($data = null)
    {
        $config = new SourceConfig();
        if ($data !== null) {
            $config->set_data($data);
        }
        return $config;
    }
}
