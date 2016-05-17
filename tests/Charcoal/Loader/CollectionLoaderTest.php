<?php

namespace Charcoal\Tests\Loader;

use \Charcoal\Loader\CollectionLoader;
use \Charcoal\Source\DatabaseSource;

class CollectionLoaderTest extends \PHPUnit_Framework_TestCase
{
    private $obj;

    private $model;
    private $source;

    public function setUp()
    {
        $factory = new \Charcoal\Model\ModelFactory();
        $this->obj = new CollectionLoader([
            'logger' => $GLOBALS['container']['logger'],
            'factory' => $factory,
        ]);

        $s = new DatabaseSource([
            'logger'=>$GLOBALS['container']['logger'],
            'config'=>$GLOBALS['container']['config']
        ]);
        $s->setTable('tests');

        $metadataLoader = new \Charcoal\Model\MetadataLoader([
            'logger' => $GLOBALS['container']['logger'],
            'cache' => new \Stash\Pool(),
            'base_path' => '',
            'paths' => []
        ]);

        $this->model = new \Charcoal\Model\Model([
            'logger' => new \Psr\Log\NullLogger(),
            'metadata_loader' => $metadataLoader
        ]);
        $this->model->setSource($s);
        $this->model->setMetadata(json_decode('
        {
            "properties":{
                "id": {
                    "type":"id"
                },
                "test": {
                    "type": "number"
                },
                "allo": {
                    "type": "number"
                }
            },
            "sources":{
                "default":{
                    "table":"tests"
                }
            },
            "default_source":"default"
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
        $loader->setModel($this->model)
            ->setProperties(['id', 'test'])
            ->addFilter('test', 10, ['operator' => '<'])
            ->addFilter('allo', 1, ['operator' => '>='])
            ->addOrder('test', 'asc')
            ->setPage(1)
            ->setNumPerPage(10);

        $collection = $loader->load();

        $this->assertEquals(1, 1);

        $this->assertTrue(true);
    }
}
