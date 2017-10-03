<?php

namespace Charcoal\Tests\Service;

// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;

// From 'charcoal-core'
use Charcoal\Model\Service\MetadataLoader;
use Charcoal\Model\Service\ModelLoader;
use Charcoal\Model\Service\ModelLoaderBuilder;

/**
 *
 */
class ModelLoaderBuilderTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\ContainerIntegrationTrait;

    public $obj;

    public function setUp()
    {
        $container = $this->getContainer();

        $metadataLoader = new MetadataLoader([
            'logger'    => $container['logger'],
            'cache'     => $container['cache'],
            'base_path' => __DIR__,
            'paths'     => [ 'metadata' ]
        ]);

        $factory = new Factory([
            'arguments' => [[
                'logger'          => $container['logger'],
                'metadata_loader' => $metadataLoader
            ]]
        ]);

        $this->obj = new ModelLoaderBuilder([
            'factory' => $factory,
            'logger'  => $container['logger'],
            'cache'   => $container['cache']
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
