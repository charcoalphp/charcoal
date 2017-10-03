<?php

namespace Charcoal\Tests\Loader;

use ArrayIterator;
use InvalidArgumentException;
use RuntimeException;

// From Pimple
use Pimple\Container;

// From 'charcoal-config'
use Charcoal\Config\GenericConfig;

// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;

// From 'charcoal-core'
use Charcoal\Loader\CollectionLoader;
use Charcoal\Model\Collection;
use Charcoal\Model\Service\MetadataLoader;
use Charcoal\Source\DatabaseSource;

/**
 *
 */
class CollectionLoaderTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\ContainerIntegrationTrait;

    private $obj;

    private $model;
    private $source;

    public function setUp()
    {
        $container = $this->getContainer();

        $source = new DatabaseSource([
            'logger' => $container['logger'],
            'pdo'    => $container['database']
        ]);
        $source->setTable('tests');

        $factory = new Factory([
            'arguments' => [[
                'logger'          => $container['logger'],
                'metadata_loader' => $container['metadata/loader']
            ]]
        ]);

        $this->model = new \Charcoal\Model\Model([
            'container'        => $container,
            'logger'           => $container['logger'],
            'property_factory' => $container['property/factory'],
            'metadata_loader'  => $container['metadata/loader']
        ]);

        $this->obj = new CollectionLoader([
            'logger'  => $container['logger'],
            'factory' => $factory,
        ]);

        $source->setModel($this->model);

        $this->model->setSource($source);
        $this->model->setMetadata(json_decode('
        {
            "properties": {
                "id": {
                    "type": "id"
                },
                "test": {
                    "type": "number"
                },
                "allo": {
                    "type": "number"
                }
            },
            "sources": {
                "default": {
                    "table": "tests"
                }
            },
            "default_source": "default"
        }', true));

        $this->model->source()->createTable();
    }

    public function setData()
    {
        $obj = $this->obj;
        $obj->setData(
            [
                'properties' => [
                    'id',
                    'test'
                ]
            ]
        );
        $this->assertEquals(['id', 'test'], $obj->properties());
    }

    public function setDataIsChainable()
    {
        $obj = $this->obj;
        $ret = $obj->setData([]);
        $this->assertSame($ret, $obj);
    }

    public function testDefaultCollection()
    {
        $loader = $this->obj;
        $collection = $loader->createCollection();
        $this->assertInstanceOf(Collection::class, $collection);
    }

    public function testCustomCollectionClass()
    {
        $loader = $this->obj;

        $this->setExpectedException(InvalidArgumentException::class);
        $loader->setCollectionClass(false);

        $loader->setCollectionClass(\IteratorIterator::class);
        $this->setExpectedException(RuntimeException::class);
        $loader->createCollection();

        $loader->setCollectionClass(ArrayIterator::class);
        $collection = $loader->createCollection();
        $this->assertInstanceOf('\ArrayIterator', $collection);

        $loader->setCollectionClass('array');
        $collection = $loader->createCollection();
        $this->assertInternalType('array', $collection);
    }

    public function testAll()
    {
        $loader = $this->obj;
        $loader->setModel($this->model)
               ->setCollectionClass(ArrayIterator::class)
               ->setProperties(['id', 'test'])
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
}
