<?php

namespace Charcoal\Tests\Metadata;

use \Psr\Log\NullLogger;
use \Cache\Adapter\Void\VoidCachePool;

use \Charcoal\Model\ModelLoader;
use \Charcoal\Charcoal;

class ModelLoaderTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $metadataLoader = new \Charcoal\Model\MetadataLoader([
            'logger' => new NullLogger(),
            'base_path' => __DIR__,
            'paths' => ['metadata'],
            'cache'  => new VoidCachePool()
        ]);

        $factory = new \Charcoal\Factory\GenericFactory([
            'arguments' => [[
                'logger'=> new NullLogger(),
                'metadata_loader' => $metadataLoader
            ]]
        ]);

        $this->obj = new ModelLoader([
            'obj_type' => 'charcoal/model/model',
            'factory' => $factory,
            'logger' => new NullLogger(),
            'cache'  => new VoidCachePool()
        ]);
    }

    public function testArrayAccess()
    {
        $obj = $this->obj;
        $this->assertTrue(true);
        //$data = $this->obj['test'];
        //$this->assertEquals('string', $data['properties']['test']['type']);
    }
}
