<?php

namespace Charcoal\Tests\Service;

use \Psr\Log\NullLogger;
use \Cache\Adapter\Void\VoidCachePool;

use \Charcoal\Factory\GenericFactory as Factory;

use \Charcoal\Model\Service\MetadataLoader;
use \Charcoal\Model\Service\ModelLoader;
use \Charcoal\Model\Service\ModelLoaderBuilder;

/**
 *
 */
class ModelLoaderBuilderTest extends \PHPUnit_Framework_TestCase
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

        $this->obj = new ModelLoaderBuilder([
            'factory' => $factory,
            'logger' => new NullLogger(),
            'cache'  => new VoidCachePool()
        ]);
    }

    public function testBuild()
    {
        $ret = $this->obj->build('foo', 'bar');
        $this->assertInstanceOf(ModelLoader::class, $ret);
    }

    public function testInvokable()
    {
        $builder = $this->obj;
        $ret = $builder('foo', 'bar');
        $this->assertInstanceOf(ModelLoader::class, $ret);
    }
}
