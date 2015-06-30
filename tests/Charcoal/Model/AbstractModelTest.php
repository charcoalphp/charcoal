<?php

namespace Charcoal\Tests\Model;

use \Charcoal\Charcoal as Charcoal;
use \Charcoal\Source\DatabaseSource as DatabaseSource;

class AbstractModelTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    static public function setUpBeforeClass()
    {
        Charcoal::config()->add_database(
            'unit_test', [
            'username'=>'root',
            'password'=>'',
            'database'=>'charcoal_examples'
            ]
        );

        Charcoal::config()->set_default_database('unit_test');

        $s = new DatabaseSource();
        //$obj->set_model($model);
        $s->set_table('test');
        $q = 'DROP TABLE IF EXISTS `test`';
        $s->db()->query($q);

        include 'AbstractModelClass.php';
    }

    public function getObj()
    {
        $obj = new AbstractModelClass();
        $obj->set_metadata(
            [
            'properties'=>[
                'id'=>[
                    'type'=>'id'
                ],
                'foo'=>[
                    'type'=>'string'
                ]
            ],
            'key'=>'id',
            'sources'=>[
                'default'=>[
                    'table'=>'test'
                ]
            ],
            'default_source'=>'default'
            ]
        );
        $obj->source()->create_table();
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

    public function testConstructorWithData()
    {
        $obj = new AbstractModelClass(
            [
            'foo'=>'bar'
            ]
        );
        $this->assertEquals('bar', $obj->foo);
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->set_data(['foo'=>'bar']);
        $this->assertSame($ret, $obj);
        $this->assertEquals('bar', $obj->foo);

        # $this->setExpectedException('\InvalidArgumentException');
        $this->setExpectedException('\PHPUnit_Framework_Error');
        $obj->set_data(false);

    }

    public function testSetFlatData()
    {
        $obj = $this->obj;
        $ret = $obj->set_flat_data(['foo'=>'baz']);
        $this->assertSame($ret, $obj);
        $this->assertEquals('baz', $obj->foo);

        # $this->setExpectedException('\InvalidArgumentException');
        $this->setExpectedException('\PHPUnit_Framework_Error');
        $obj->set_flat_data(false);
    }

    public function testSave()
    {
        $obj = $this->obj;
        $obj->set_data(
            [
            'id'=>1,
            'foo'=>'Test'
            ]
        );
        $ret = $obj->save();
        $this->assertEquals(1, $ret);
    }

    public function testLoad()
    {
        $obj = $this->obj;
        $ret = $obj->load(1);
        //var_dump($ret);
        $this->assertEquals('Test', $obj->foo);
    }

    public function testUpdate()
    {
        $obj = $this->obj;
        $obj->set_data(
            [
            'id'=>1,
            'foo'=>'Foobar'
            ]
        );
        $ret = $obj->update();
        $this->assertTrue($ret);

        $obj2 = $this->getObj();
        $obj2->load(1);
        $this->assertEquals('Foobar', $obj2->foo);
    }

    public function testDelete()
    {
        $obj = $this->obj;
        $obj->set_data(
            [
            'id'=>1
            ]
        );
        $ret = $obj->delete();
        $this->assertTrue($ret);
    }
}
