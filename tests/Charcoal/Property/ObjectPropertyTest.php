<?php

namespace Charcoal\Tests\Property;

use PHPUnit_Framework_TestCase;

use PDO;

use Psr\Log\NullLogger;
use Cache\Adapter\Void\VoidCachePool;

use Pimple\Container;

use Charcoal\Factory\GenericFactory as Factory;

use Charcoal\Loader\CollectionLoader;

use Charcoal\Model\Service\MetadataLoader;
use Charcoal\Source\DatabaseSource;

use Charcoal\Property\ObjectProperty;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class ObjectPropertyTest extends PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $container = new Container;
        $container['translator'] = $GLOBALS['translator'];
        $container['metadata/loader'] = function (Container $container) {
            return new MetadataLoader([
                'logger' => new NullLogger(),
                'base_path' => realpath(__DIR__.'/../../../'),
                    'paths' => [
                        'metadata'
                    ],
                'cache'  => $container['cache']
            ]);
        };
        $container['source/factory'] = function ($container) {
            return new Factory([
                'map' => [
                    'database' => DatabaseSource::class
                ],
                'arguments'  => [[
                    'logger' => new NullLogger(),
                    'cache'  => new VoidCachePool(),
                    'pdo'    => new PDO('sqlite::memory:')
                ]]
            ]);
        };
        $container['property/factory'] = function (Container $container) {
            return new Factory([
                'resolver_options' => [
                    'prefix' => '\Charcoal\Property\\',
                    'suffix' => 'Property'
                ],
                'arguments' => [[
                    'container' => $container,
                    'logger'    => new NullLogger()
                ]]
            ]);
        };
        $container['model/factory'] = function (Container $container) {
            return new Factory([
                'arguments' => [[
                    'logger'            => new NullLogger(),
                    'metadata_loader'   => $container['metadata/loader'],
                    'source_factory'    => $container['source/factory'],
                    'property_factory'  => $container['property/factory'],
                    'caontainer'        => $container
                ]]
            ]);
        };
        $container['model/collection/loader'] = function (Container $container) {
            return new CollectionLoader([
                'logger' => new NullLogger(),
                'cache' => new VoidCachePool()
            ]);
        };
        $container['cache'] = function (Container $container) {
            return new VoidCachePool();
        };

        $this->obj = new ObjectProperty([
            'container' => $container,
            'database' => new PDO('sqlite::memory:'),
            'logger' => new NullLogger(),
            'translator' => $GLOBALS['translator']
        ]);
    }

    public function testType()
    {
        $this->assertEquals('object', $this->obj->type());
    }

    public function testAccessingObjTypeBeforeSetterThrowsException()
    {
        $this->setExpectedException('\Exception');
        $this->obj->objType();
    }

    public function testSetObjType()
    {
        $ret = $this->obj->setObjType('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->objType());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setObjType(false);
    }

    public function testSetPattern()
    {
        $ret = $this->obj->setPattern('{{foo}}');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('{{foo}}', $this->obj->pattern());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setPattern([]);
    }

    public function testSqlExtra()
    {
        $this->assertEquals('', $this->obj->sqlExtra());
    }

    // public function testSqlType()
    // {
    //     $this->obj->setObjType('charcoal/model/model');
    //     $this->assertEquals('', $this->obj->sqlType());
    // }

    public function testSqlTypeMultiple()
    {
        $this->obj->setMultiple(true);
        $this->assertEquals('TEXT', $this->obj->sqlType());
    }

    public function testParseOneWithScalarValue()
    {
        $this->assertEquals('foobar', $this->obj->parseOne('foobar'));

        $mock = $this->getMock('\Charcoal\Source\StorableInterface');
        $this->assertNull($this->obj->parseOne($mock));

        // Force ID to 'foo'.
        $mock->expects($this->any())
            ->method('id')
            ->will($this->returnValue('foo'));
        $this->assertEquals('foo', $this->obj->parseOne($mock));
    }

    public function testParseOneWithObjectWithoutIdReturnsNull()
    {
        $mock = $this->getMock('\Charcoal\Source\StorableInterface');
        $this->assertNull($this->obj->parseOne($mock));
    }

    public function testParseOneWithObjectWithIdReturnsId()
    {
        $mock = $this->getMock('\Charcoal\Source\StorableInterface');
        $mock->expects($this->any())
            ->method('id')
            ->will($this->returnValue('foo'));
        $this->assertEquals('foo', $this->obj->parseOne($mock));
    }

    public function testStorageVal()
    {
        $this->assertNull($this->obj->storageVal(''));
        $this->assertNull($this->obj->storageVal(null));
    }
}
