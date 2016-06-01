<?php

namespace Charcoal\Tests\Metadata;

use \Psr\Log\NullLogger;
use \Cache\Adapter\Void\VoidCachePool;

use \Charcoal\Model\MetadataLoader;
use \Charcoal\Charcoal;

class MetadataLoaderTest extends \PHPUnit_Framework_TestCase
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

    public function testLoadData()
    {
        $data = $this->obj->loadData('test');
        $this->assertEquals('string', $data['properties']['test']['type']);
    }
}
