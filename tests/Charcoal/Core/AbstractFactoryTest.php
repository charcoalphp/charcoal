<?php

namespace Charcoal\Tests\Core;

use \Charcoal\Core\AbstractFactory as AbstractFactory;

use \Charcoal\Tests\Core\AbstractFactoryClass as AbstractFactoryClass;


class AbstractFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        include 'AbstractFactoryClass.php';
        $this->obj = AbstractFactoryClass::instance();
    }

    public function testGetInvalidTypeThrowsException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->get('foo');
    }
}
