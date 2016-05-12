<?php

namespace Charcoal\Tests\Model;

use \Charcoal\Charcoal;
use \Charcoal\Source\DatabaseSource;

class AbstractModelTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public static function setUpBeforeClass()
    {
        $app = $GLOBALS['app'];
        $s = new DatabaseSource([
            'logger'=>$GLOBALS['container']['logger'],
            'config'=>$GLOBALS['container']['config']
        ]);
        $s->setTable('test');
        $q = 'DROP TABLE IF EXISTS `test`';
        $s->db()->query($q);

        include_once 'AbstractModelClass.php';
    }

    public function getObj()
    {
        $s = new DatabaseSource([
            'logger'=>$GLOBALS['container']['logger'],
            'config'=>$GLOBALS['container']['config']
        ]);
        $s->setTable('tests');

        $obj = new AbstractModelClass();
        $obj->setSource($s);
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
        $obj->setData(
            [
                'id'  => 1,
                'foo' => 'Test'
            ]
        );
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
            'id'=>42,
            'foo'=>'Bar'
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
            'id'=>42,
            'foo'=>'Bar'
        ];
        $obj->setData($data);
        $json = json_encode($obj);
        $this->assertEquals(json_encode($data), $json);

    }
}
