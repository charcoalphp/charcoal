<?php

namespace Charcoal\Tests\Config\Config;

// From 'charcoal-config'
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\FixturesTrait;
use Charcoal\Tests\Config\Mock\MacroConfig;
use Charcoal\Config\AbstractConfig;

/**
 * Base AbstractConfig Test
 */
abstract class AbstractConfigTestCase extends AbstractTestCase
{
    use FixturesTrait;

    /**
     * Create a concrete MacroConfig instance.
     *
     * @param  mixed $data      Data to pre-populate the object.
     * @param  array $delegates Delegates to pre-populate the object.
     * @return MacroConfig
     */
    public function createConfig($data = null, array $delegates = null)
    {
        return new MacroConfig($data, $delegates);
    }

    /**
     * Create a mock instance of AbstractConfig.
     *
     * @param  mixed $data      Data to pre-populate the object.
     * @param  mixed $delegates Delegates to pre-populate the object.
     * @return AbstractConfig
     */
    public function mockConfig($data = null, $delegates = null)
    {
        return $this->getMockForAbstractClass(AbstractConfig::class, [ $data, $delegates ]);
    }
}
