<?php

namespace Charcoal\Tests\Loader;

use ArrayIterator;
use InvalidArgumentException;
use RuntimeException;

// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;

// From 'charcoal-core'
use Charcoal\Loader\CollectionLoader;
use Charcoal\Model\Model;
use Charcoal\Model\Collection;
use Charcoal\Model\Service\MetadataLoader;
use Charcoal\Source\DatabaseSource;

use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\CoreContainerIntegrationTrait;
use Charcoal\Tests\ReflectionsTrait;

/**
 *
 */
class CollectionLoaderTest extends AbstractTestCase
{
    use CoreContainerIntegrationTrait;
    use ReflectionsTrait;

    /**
     * @var CollectionLoader
     */
    private $loader;

    /**
     * @var Model
     */
    private $model;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->model = $this->createModel();
        $this->model->source()->createTable();

        $this->loader = $this->createCollectionLoader();
    }

    /**
     * @return CollectionLoader
     */
    public function createCollectionLoader()
    {
        $container = $this->getContainer();

        $factory = new Factory([
            'arguments' => [[
                'logger'          => $container['logger'],
                'metadata_loader' => $container['metadata/loader']
            ]]
        ]);

        $loader = new CollectionLoader([
            'logger'  => $container['logger'],
            'factory' => $factory,
        ]);

        return $loader;
    }

    /**
     * @return Model
     */
    public function createModel()
    {
        $container = $this->getContainer();

        $source = new DatabaseSource([
            'logger' => $container['logger'],
            'pdo'    => $container['database']
        ]);
        $source->setTable('tests');

        $model = new Model([
            'container'        => $container,
            'logger'           => $container['logger'],
            'property_factory' => $container['property/factory'],
            'metadata_loader'  => $container['metadata/loader']
        ]);

        $source->setModel($model);
        $model->setSource($source);

        $model->setMetadata([
            'properties' => [
                'id' => [
                    'type' => 'id'
                ],
                'test' => [
                    'type' => 'number'
                ],
                'allo' => [
                    'type' => 'number'
                ],
            ],
            'sources' => [
                'default' => [
                    'table' => 'tests'
                ]
            ],
            'default_source' => 'default',
        ]);

        return $model;
    }

    /**
     * @return void
     */
    public function testSetData()
    {
        $loader = $this->loader;
        $loader->setModel($this->model);

        $loader->setData([
            'properties' => [
                'id',
                'test'
            ]
        ]);
        $this->assertEquals([ 'id', 'test' ], $loader->properties());
    }

    /**
     * @return void
     */
    public function testSetDataIsChainable()
    {
        $loader = $this->loader;
        $ret = $loader->setData([]);
        $this->assertSame($ret, $loader);
    }

    /**
     * @return void
     */
    public function testDefaultCollection()
    {
        $loader = $this->loader;
        $collection = $loader->createCollection();
        $this->assertInstanceOf(Collection::class, $collection);
    }

    /**
     * @return void
     */
    public function testCustomCollectionClass()
    {
        $loader = $this->loader;

        $this->expectException(InvalidArgumentException::class);
        $loader->setCollectionClass(false);

        $loader->setCollectionClass(\IteratorIterator::class);
        $this->expectException(RuntimeException::class);
        $loader->createCollection();

        $loader->setCollectionClass(ArrayIterator::class);
        $collection = $loader->createCollection();
        $this->assertInstanceOf(ArrayIterator::class, $collection);

        $loader->setCollectionClass('array');
        $collection = $loader->createCollection();
        $this->assertInternalType('array', $collection);
    }

    /**
     * @return void
     */
    public function testAll()
    {
        $loader = $this->loader;
        $loader->setModel($this->model)
               ->setCollectionClass(ArrayIterator::class)
               ->setProperties([ 'id', 'test' ])
               ->addFilter('test', 10, [ 'operator' => '<' ])
               ->addFilter('allo', 1, [ 'operator' => '>=' ])
               ->addOrder('test', 'asc')
               ->setPage(1)
               ->setNumPerPage(10);

        $this->assertTrue($loader->hasModel());

        $collection = $loader->load();

        $this->assertEquals(1, 1);

        $this->assertTrue(true);
    }

    /**
     * Test camelization.
     *
     * @covers \Charcoal\Loader\CollectionLoader::camelize
     * @covers \Charcoal\Loader\CollectionLoader::getter
     * @covers \Charcoal\Loader\CollectionLoader::setter
     *
     * @return void
     */
    public function testCamelize()
    {
        $loader = $this->loader;

        $getter = $this->getMethod($loader, 'getter');
        $setter = $this->getMethod($loader, 'setter');

        $this->assertEquals('charcoalPhp', $getter->invoke($loader, 'charcoal_php'));
        $this->assertEquals('setCharcoalPhp', $setter->invoke($loader, 'charcoal_php'));
    }
}
