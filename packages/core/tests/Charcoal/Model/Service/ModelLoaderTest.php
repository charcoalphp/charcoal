<?php

namespace Charcoal\Tests\Model\Service;

use Exception;

// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;

// From 'charcoal-core'
use Charcoal\Model\Service\ModelLoader;
use Charcoal\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModelLoader::class)]
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
                'logger'          => $container->get('logger'),
                'metadata_loader' => $container->get('metadata/loader')
            ]]
        ]);

        $this->obj = new ModelLoader([
            'obj_type' => 'charcoal/model/model',
            'factory'  => $factory,
            'logger'   => $container->get('logger'),
            'cache'    => $container->get('cache')
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
