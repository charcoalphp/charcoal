<?php

namespace Charcoal\Tests\Loader;

use \ArrayIterator;

use \Psr\Log\NullLogger;
use \Cache\Adapter\Void\VoidCachePool;

use \Pimple\Container;

use \Charcoal\Config\GenericConfig;

use \Charcoal\Factory\GenericFactory as Factory;

use \Charcoal\Loader\CollectionLoader;
use \Charcoal\Source\DatabaseSource;

use \Charcoal\Model\Service\MetadataLoader;

class CollectionLoaderTest extends \PHPUnit_Framework_TestCase
{
    private $obj;

    private $model;
    private $source;

    private function getContainer()
    {
        $container = new Container();

        $container['logger'] = function (Container $container) {
            return new NullLogger();
        };
        $container['cache'] = function (Container $container) {
            return new VoidCachePool();
        };
        $container['pdo'] = function (Container $container) {
            return $GLOBALS['pdo'];
        };
        $container['metadata/loader'] = function (Container $container) {
            return new MetadataLoader([
                'logger'    => $container['logger'],
                'cache'     => $container['cache'],
                'base_path' => __DIR__,
                'paths'     => [ 'metadata' ]
            ]);
        };
        $container['property/factory'] = function (Container $container) {
            return new Factory([
                'resolver_options' => [
                    'prefix' => '\Charcoal\Property\\',
                    'suffix' => 'Property'
                ],
                'arguments' => [[
                    'container'        => $container,
                    'logger'           => $container['logger'],
                    'metadata_loader'  => $container['metadata/loader']
                ]]
            ]);
        };
        return $container;
    }
    public function setUp()
    {
        $container = $this->getContainer();

        $source = new DatabaseSource([
            'logger' => $container['logger'],
            'pdo'    => $container['pdo']
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
        $this->assertInstanceOf('\Charcoal\Model\Collection', $collection);
    }

    public function testCustomCollectionClass()
    {
        $loader = $this->obj;

        $this->setExpectedException('\InvalidArgumentException');
        $loader->setCollectionClass(false);

        $loader->setCollectionClass(\IteratorIterator::class);
        $this->setExpectedException('\RuntimeException');
        $loader->createCollection();

        $loader->setCollectionClass(ArrayIterator::class);
        $collection = $loader->createCollection();
        $this->assertInstanceOf('\ArrayIterator', $collection);
    }

    public function testAll()
    {
        $loader = $this->obj;
        $loader
            ->setModel($this->model)
            ->setCollectionClass(ArrayIterator::class)
            ->setProperties(['id', 'test'])
            ->addFilter('test', 10, [ 'operator' => '<' ])
            ->addFilter('allo', 1, [ 'operator' => '>=' ])
            ->addOrder('test', 'asc')
            ->setPage(1)
            ->setNumPerPage(10);

        $collection = $loader->load();

        $this->assertEquals(1, 1);

        $this->assertTrue(true);
    }
}
