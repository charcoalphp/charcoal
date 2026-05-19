<?php

declare(strict_types=1);

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

    public function testLoadData(): void
    {
        $this->assertInstanceOf(MetadataLoader::class, $this->obj);
        //$ret = $this->obj->load('test', $this->);
    }
}
