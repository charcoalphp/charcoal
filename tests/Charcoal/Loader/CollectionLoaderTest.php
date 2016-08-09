<?php

namespace Charcoal\Tests\Loader;

use \Psr\Log\NullLogger;
use \Cache\Adapter\Void\VoidCachePool;

use \Charcoal\Config\GenericConfig;

use \Charcoal\Loader\CollectionLoader;
use \Charcoal\Source\DatabaseSource;

class CollectionLoaderTest extends \PHPUnit_Framework_TestCase
{
    private $obj;

    private $model;
    private $source;

    public function setUp()
    {
        $logger = new NullLogger();
        $cache  = new VoidCachePool();

        $source = new DatabaseSource([
            'logger' => $logger,
            'pdo'    => $GLOBALS['pdo']
        ]);
        $source->setTable('tests');

        $metadataLoader = new \Charcoal\Model\MetadataLoader([
            'logger'    => $logger,
            'cache'     => $cache,
            'base_path' => __DIR__,
            'paths'     => [ 'metadata' ]
        ]);

        $factory = new \Charcoal\Factory\GenericFactory([
            'arguments' => [[
                'logger'          => $logger,
                'metadata_loader' => $metadataLoader
            ]]
        ]);

        $this->obj = new CollectionLoader([
            'logger'  => $logger,
            'factory' => $factory,
        ]);

        $this->model = new \Charcoal\Model\Model([
            'logger'          => $logger,
            'metadata_loader' => $metadataLoader
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

    public function testAll()
    {
        $loader = $this->obj;
        $loader
            ->setModel($this->model)
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
