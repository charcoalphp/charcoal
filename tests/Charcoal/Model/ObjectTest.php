<?php

namespace Charcoal\Tests\Model;

use \Charcoal\Model\Object;

class ObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Object();
        $this->assertInstanceOf('\Charcoal\Model\Object', $obj);
        $this->assertInstanceOf('\Charcoal\Model\Model', $obj);
    }

    /*public function testIdDefaultValueIsNull()
    {
        $obj = new Object();
        $obj->set_key('id');
        // $this->assertEquals(null, $obj->id());
        $this->assertTrue(true);
    }

    /**
    * @dataProvider validIdProvider
    *
    public function testIdGetterSetter($valid_id)
    {
        $obj = new Object();
        $obj->set_key('id');
        $obj->set_id($valid_id);
        $this->assertEquals($valid_id, $obj->id());
    }*/

    /**
    * @dataProvider invalidIdProvider
    */
    public function testSetIdInvalidParameterThrowException($invalid_id)
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = new Object();
        $obj->set_key('id');
        $obj->set_id($invalid_id);
    }

    public function testSetIdIsChainable()
    {
        $obj = new Object();
        $obj->set_key('id');
        $obj2 = $obj->set_id('test');

        $this->assertSame($obj, $obj2);
    }

    public function validIdProvider()
    {
        return [
            [1],
            ['string'],
            ['string with space'],
            ['Ütf8 Strîngs @ƒ˙ƒ ˙ø ª ™ß»¢'],
            ["\0"]
        ];
    }

    public function invalidIdProvider()
    {
        $obj = new \StdClass();
        return [
            [[]],
            [null],
            [$obj]
        ];
    }
}
