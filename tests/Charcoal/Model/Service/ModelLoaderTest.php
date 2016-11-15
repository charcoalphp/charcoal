<?php

namespace Charcoal\Tests\Model\Service;

use \Psr\Log\NullLogger;
use \Cache\Adapter\Void\VoidCachePool;

use \Charcoal\Factory\GenericFactory as Factory;

use \Charcoal\Model\Service\MetadataLoader;
use \Charcoal\Model\Service\ModelLoader;

/**
 *
 */
class ModelLoaderTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {

        $metadataLoader = new MetadataLoader([
            'logger' => new NullLogger(),
            'base_path' => __DIR__,
            'paths' => ['metadata'],
            'cache'  => new VoidCachePool()
        ]);

        $factory = new Factory([
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

    public function testLoadInvalidObjTypeThrowsException()
    {
        $this->setExpectedException('\Exception');
        $this->obj->load('foobar');
    }
}
