<?php

namespace Charcoal\Tests\Model;

class AbstractModelTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    static public function setUpBeforeClass()
    {
        include 'AbstractModelClass.php';
    }

    public function setUp()
    {
        $this->obj = new AbstractModelClass();
        $this->obj->set_metadata([
            'properties'=>[
                'foo'=>[
                    'type'=>'string'
                ]
            ]
        ]);
    }

    public function testConstructor()
    {
        $obj = $this->obj;
        $this->assertInstanceOf('\Charcoal\Model\AbstractModel', $obj);
    }

    public function testConstructorWithData()
    {
        $obj = new AbstractModelClass([
            'foo'=>'bar'
        ]);
        $this->assertEquals('bar', $obj->foo);
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->set_data(['foo'=>'bar']);
        $this->assertSame($ret, $obj);
        $this->assertEquals('bar', $obj->foo);

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_data(false);

    }
}
