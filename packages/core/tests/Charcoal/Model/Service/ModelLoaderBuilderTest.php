<?php

namespace Charcoal\Tests\Service;

// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;

// From 'charcoal-core'
use Charcoal\Model\Service\MetadataLoader;
use Charcoal\Model\Service\ModelLoader;
use Charcoal\Model\Service\ModelLoaderBuilder;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Mock\GenericModel;

/**
 *
 */
class ModelLoaderBuilderTest extends AbstractTestCase
{
    use \Charcoal\Tests\CoreContainerIntegrationTrait;

    /**
     * @var ModelLoaderBuilder
     */
    public $obj;

    /**
     * @return void
     */
    protected function setUp(): void
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

    /**
     * @return void
     */
    public function testBuild()
    {
        $ret = $this->obj->build(GenericModel::class, 'name');
        $this->assertInstanceOf(ModelLoader::class, $ret);
    }

    /**
     * @return void
     */
    public function testInvokable()
    {
        $builder = $this->obj;
        $ret = $builder(GenericModel::class);
        $this->assertInstanceOf(ModelLoader::class, $ret);
    }
}
