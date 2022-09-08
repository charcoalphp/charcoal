<?php

namespace Charcoal\Tests\Model\Service;

use Exception;

// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;

// From 'charcoal-core'
use Charcoal\Model\Service\ModelLoader;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class ModelLoaderTest extends AbstractTestCase
{
    use \Charcoal\Tests\CoreContainerIntegrationTrait;

    /**
     * Tested Class.
     *
     * @var ModelLoader
     */
    public $obj;

    /**
     * Set up the test.
     *
     * @return void
     */
    protected function setUp(): void
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

    /**
     * @return void
     */
    public function testLoadInvalidObjTypeThrowsException()
    {
        $this->expectException(Exception::class);
        $this->obj->load('foobar');
    }
}
