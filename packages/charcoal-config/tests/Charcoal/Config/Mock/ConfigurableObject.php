<?php

namespace Charcoal\Tests\Config\Mock;

// From 'charcoal-config'
use Charcoal\Config\ConfigurableInterface;
use Charcoal\Config\ConfigurableTrait;

/**
 * Mock object of {@see \Charcoal\Tests\Config\ConfigurableTest}
 */
class ConfigurableObject implements ConfigurableInterface
{
    use ConfigurableTrait {
        ConfigurableTrait::createConfig as public;
    }
}
