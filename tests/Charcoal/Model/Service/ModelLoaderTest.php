<?php

namespace Charcoal\Tests\Model\Service;

use \Charcoal\Factory\GenericFactory as Factory;

use \Charcoal\Model\Service\MetadataLoader;
use \Charcoal\Model\Service\ModelLoader;

/**
 *
 */
class ModelLoaderTest extends \PHPUnit_Framework_TestCase
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

        $this->obj = new ModelLoader([
            'obj_type' => 'charcoal/model/model',
            'factory'  => $factory,
            'logger'   => $container['logger']
        ]);
    }

    public function testLoadInvalidObjTypeThrowsException()
    {
        $this->setExpectedException('\Exception');
        $this->obj->load('foobar');
    }
}
