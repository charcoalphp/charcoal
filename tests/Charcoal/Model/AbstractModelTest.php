<?php

namespace Charcoal\Tests\Model;

use \Psr\Log\NullLogger;
use \Cache\Adapter\Void\VoidCachePool;

use \Pimple\Container;

use \Charcoal\Config\GenericConfig;

use \Charcoal\Factory\GenericFactory as Factory;

use \Charcoal\Charcoal;
use \Charcoal\Source\DatabaseSource;
use \Charcoal\Model\Service\MetadataLoader;

use \Charcoal\Property\PropertyInterface;
use \Charcoal\Property\GenericProperty;

class AbstractModelTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public static function setUpBeforeClass()
    {
        include_once 'AbstractModelClass.php';
    }

    private function getContainer()
    {
        $container = new Container();

        $container['logger'] = function (Container $container) {
            return new NullLogger();
        };
        $container['cache'] = function (Container $container) {
            return new VoidCachePool();
        };
        $container['database'] = function (Container $container) {
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
                    'database'         => $container['database'],
                    'metadata_loader'  => $container['metadata/loader']
                ]]
            ]);
        };

        return $container;
    }

    private function getObj()
    {
        $container = $this->getContainer();

        $source = new DatabaseSource([
            'logger' => $container['logger'],
            'pdo'    => $container['database']
        ]);
        $source->setTable('test');
        $source->db()->query('DROP TABLE IF EXISTS `test`');

        $obj = new AbstractModelClass([
            'container'        => $container,
            'logger'           => $container['logger'],
            'property_factory' => $container['property/factory'],
            'metadata_loader'  => $container['metadata/loader']
        ]);

        $source->setModel($obj);

        $obj->setSource($source);
        $obj->setMetadata(
            [
                'properties' => [
                    'id' => [
                        'type' => 'id'
                    ],
                    'foo' => [
                        'type' => 'string'
                    ]
                ],
                'key' => 'id',
                'sources' => [
                    'default' => [
                        'table' => 'test'
                    ]
                ],
                'default_source' => 'default'
            ]
        );
        $obj->source()->setModel($obj);

        if ($obj->source()->tableExists() === false) {
            $obj->source()->createTable();
        }

        return $obj;
    }

    public function setUp()
    {
        $this->obj = $this->getObj();
    }

    public function testConstructor()
    {
        $obj = $this->obj;
        $this->assertInstanceOf('\Charcoal\Model\AbstractModel', $obj);
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData(['foo' => 'bar']);
        $this->assertSame($ret, $obj);
        $this->assertEquals('bar', $obj->foo);
    }

    public function testSetFlatData()
    {
        $obj = $this->obj;
        $ret = $obj->setFlatData(['foo' => 'baz']);
        $this->assertSame($ret, $obj);
        $this->assertEquals('baz', $obj->foo);
    }

    public function testSave()
    {
        $obj = $this->obj;
        $obj->setData([
            'id'  => 1,
            'foo' => 'Test'
        ]);
        $ret = $obj->save();

        $this->assertEquals(1, $ret);
    }

    // public function testLoad()
    // {
    //     $obj = $this->obj;
    //     $ret = $obj->load(1);
    //     // var_dump($ret);
    //     $this->assertEquals('Test', $obj->foo);
    // }

    // public function testUpdate()
    // {
    //     $obj = $this->obj;
    //     $obj->setData(
    //         [
    //             'id'  => 1,
    //             'foo' => 'Foobar'
    //         ]
    //     );
    //     $ret = $obj->update();
    //     $this->assertTrue($ret);

    //     $obj2 = $this->getObj();
    //     $obj2->load(1);
    //     $this->assertEquals('Foobar', $obj2->foo);
    // }

    public function testDelete()
    {
        $obj = $this->obj;
        $obj->setData(
            [
                'id' => 1
            ]
        );
        $ret = $obj->delete();
        $this->assertTrue($ret);
    }

    public function testSerializeUnserialize()
    {
        $obj = $this->obj;
        $obj->setData([
            'id'  => 42,
            'foo' => 'Bar'
        ]);
        $serialized = serialize($obj);
        $this->assertEquals('C:39:"Charcoal\Tests\Model\AbstractModelClass":40:{a:2:{s:2:"id";i:42;s:3:"foo";s:3:"Bar";}}', $serialized);
        $obj2 = unserialize($serialized);

        //$this->assertEquals($obj, $obj2);
        $this->assertEquals(42, $obj2->id);
        $this->assertEquals('Bar', $obj2->foo);
    }

    public function testJsonSerialize()
    {
        $obj = $this->obj;
        $data = [
            'id'  => 42,
            'foo' => 'Bar'
        ];
        $obj->setData($data);
        $json = json_encode($obj);
        $this->assertEquals(json_encode($data), $json);
    }
}
