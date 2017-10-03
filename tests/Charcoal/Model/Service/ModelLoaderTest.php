<?php

namespace Charcoal\Tests\Model\Service;

use Exception;

// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;

// From 'charcoal-core'
use Charcoal\Model\Service\ModelLoader;

/**
 *
 */
class ModelLoaderTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\ContainerIntegrationTrait;

    /**
     * Tested Class.
     *
     * @var ModelLoader
     */
    public $obj;

    /**
     * Set up the test.
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $factory = new Factory([
            'arguments' => [[
                'logger'          => $container['logger'],
                'metadata_loader' => $container['metadata/loader']
            ]]
        ]);

        $this->obj = new ModelLoader([
            'obj_type' => 'charcoal/model/model',
            'factory'  => $factory,
            'logger'   => $container['logger'],
            'cache'    => $container['cache']
        ]);
    }

    public function testLoadInvalidObjTypeThrowsException()
    {
        $this->setExpectedException(Exception::class);
        $this->obj->load('foobar');
    }
}
