<?php

namespace Charcoal\Tests\Service;

use \Charcoal\Model\Service\MetadataLoader;

/**
 *
 */
class MetadataLoaderTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\ContainerIntegrationTrait;

    public $obj;

    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new MetadataLoader([
            'logger'    => $container['logger'],
            'cache'     => $container['cache'],
            'base_path' => __DIR__,
            'paths'     => [ 'metadata' ]
        ]);
    }

    public function testLoadData()
    {
        $this->assertInstanceOf(MetadataLoader::class, $this->obj);
    }
}
