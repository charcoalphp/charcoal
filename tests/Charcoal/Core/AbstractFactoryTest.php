<?php

namespace Charcoal\Tests\Core;

use \Charcoal\Core\AbstractFactory as AbstractFactory;

include 'AbstractFactoryClass.php';
use \Charcoal\Tests\Core\AbstractFactoryClass as AbstractFactoryClass;



class AbstractFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->obj = new AbstractFactoryClass();
    }

    public function testGetInvalidTypeThrowsException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->get('foo');
    }
}
