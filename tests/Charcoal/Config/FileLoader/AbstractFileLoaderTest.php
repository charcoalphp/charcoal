<?php

namespace Charcoal\Tests\Config\FileLoader;

// From 'charcoal-config'
use Charcoal\Tests\Config\AbstractConfigTest;
use Charcoal\Config\GenericConfig;

/**
 * Base Config File Loading Test
 */
abstract class AbstractFileLoaderTest extends AbstractConfigTest
{
    /**
     * @var GenericConfig
     */
    public $cfg;

    /**
     * Create a concrete GenericConfig instance.
     *
     * @return void
     */
    public function setUp()
    {
        $this->cfg = $this->createConfig();
    }

    /**
     * Create a GenericConfig instance.
     *
     * @param  mixed $data      Data to pre-populate the object.
     * @param  array $delegates Delegates to pre-populate the object.
     * @return GenericConfig
     */
    public function createConfig($data = null, array $delegates = null)
    {
        return new GenericConfig($data, $delegates);
    }
}
