<?php

namespace Charcoal\Tests\Service;

use \Psr\Log\NullLogger;

use \Cache\Adapter\Void\VoidCachePool;

use \Charcoal\Model\Service\MetadataLoader;

/**
 *
 */
class CollectionLoaderTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new MetadataLoader([
            'logger' => new NullLogger(),
            'base_path' => __DIR__,
            'paths' => ['metadata'],
            'cache'  => new VoidCachePool()
        ]);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(MetadataLoader::class, $this->obj);
    }
}
