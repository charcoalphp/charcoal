<?php

namespace Charcoal\Tests\Service;

// From 'charcoal-core'
use Charcoal\Model\Service\MetadataLoader;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class MetadataLoaderTest extends AbstractTestCase
{
    use \Charcoal\Tests\CoreContainerIntegrationTrait;

    /**
     * @var MetadataLoader
     */
    public $obj;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $container = $this->getContainer();

        $this->obj = new MetadataLoader([
            'logger'    => $container['logger'],
            'cache'     => $container['cache'],
            'base_path' => __DIR__,
            'paths'     => [ 'metadata' ]
        ]);
    }

    /**
     * @return void
     */
    public function testLoadData()
    {
        $this->assertInstanceOf(MetadataLoader::class, $this->obj);
        //$ret = $this->obj->load('test', $this->);
    }
}
