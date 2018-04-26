<?php

namespace Charcoal\Tests\Config;

// From 'charcoal-config'
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Config\Mock\MacroConfig;
use Charcoal\Config\AbstractConfig;

/**
 * Base AbstractConfig Test
 */
abstract class AbstractConfigTest extends AbstractTestCase
{
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

    /**
     * Retrieve the file path to the given fixture.
     *
     * @param  string $file The file path relative to the Fixture directory.
     * @return string The file path to the fixture relative to the base directory.
     */
    public function getPathToFixture($file)
    {
        return 'tests/Charcoal/Config/Fixture/'.ltrim($file, '/');
    }
}
