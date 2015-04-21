<?php

namespace Charcoal\Tests\Core;

use \Charcoal\Core\AbstractFactory as AbstractFactory;

use \Charcoal\Tests\Core\AbstractFactoryClass as AbstractFactoryClass;


class AbstractFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        include_once 'AbstractFactoryClass.php';
        $this->obj = AbstractFactoryClass::instance();
    }

    public function testGetInvalidTypeThrowsException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->get('foo');
    }

    public function testGetInvalidParameterThrowsException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->get(false);
    }

    public function testCreate()
    {
        $obj = $this->obj;
        $this->setExpectedException('\InvalidArgumentException');
        $obj->create(false);
    }

    /*public function testSingleton()
    {
        $obj1 = AbstractFactoryClass::instance();
        $obj2 = AbstractFactoryClass::instance();
        $this->assertSame($obj1, $obj2);
    }*/
}
