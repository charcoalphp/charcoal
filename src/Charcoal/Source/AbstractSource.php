<?php

namespace Charcoal\Source;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Config\ConfigurableInterface as ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait as ConfigurableTrait;

// Local namespace dependencies
use \Charcoal\Source\SourceConfig as SourceConfig;
use \Charcoal\Source\SourceInterface as SourceInterface;

/**
*
*/
abstract class AbstractSource implements
    SourceInterface,
    ConfigurableInterface
{
    use ConfigurableTrait;

    /**
    * ConfigurableTrait > create_config()
    *
    * @param array $data Optional
    * @return SourceConfig
    */
    public function create_config(array $data = null)
    {
        $config = new SourceConfig();
        if (is_array($data)) {
            $config->set_data($data);
        }
        return $config;
    }
}
