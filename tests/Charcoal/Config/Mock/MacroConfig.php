<?php

namespace Charcoal\Tests\Config\Mock;

// From 'charcoal-config'
use Charcoal\Config\AbstractConfig;
use Charcoal\Tests\Entity\Mock\MacroTrait;

/**
 * Mock object of {@see \Charcoal\Config\AbstractConfig}
 */
class MacroConfig extends AbstractConfig
{
    use MacroTrait;

    /**
     * @return array
     */
    public function defaults()
    {
        return [
            'foo' => -3,
            'baz' => 'garply',
            'erd' => true,
        ];
    }
}
