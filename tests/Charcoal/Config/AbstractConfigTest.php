<?php

namespace Charcoal\Tests\Config;

use \Charcoal\Model\Model as Model;
use \Charcoal\Model\ModelMetadata as Metadata;

class AbstractConfigTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    static public function setUpBeforeClass()
    {
        include_once 'AbstractConfigClass.php';
    }

    public function setUp()
    {
        $this->obj = new AbstractConfigClass();
    }

    public function testConstructor()
    {
        $obj = $this->obj;
        $this->assertInstanceOf('\Charcoal\Config\AbstractConfig', $obj);

        $this->setExpectedException('\InvalidArgumentException');
        $obj2 = new AbstractConfigClass(false);
    }

    public function testArrayAccess()
    {
        $obj = $this->obj;
        $obj['foo'] = 'test';
        $this->assertEquals('test', $obj['foo']);

        $this->assertTrue(isset($obj['foo']));
        unset($obj['foo']);
        $this->assertNotTrue(isset($obj['foo']));
    }

    public function testArrayAccessGetNumericException()
    {
        $obj = $this->obj;
        $this->setExpectedException('\InvalidArgumentException');
        $obj[0];
    }

    public function testArrayAccessSetNumericException()
    {
        $obj = $this->obj;
        $this->setExpectedException('\InvalidArgumentException');
        $obj[0] = 'foo';
    }

    public function testArrayAccessIssetNumericException()
    {
        $obj = $this->obj;
        $this->setExpectedException('\InvalidArgumentException');
        isset($obj[0]);
    }

    public function testArrayAccessUnsetNumericException()
    {
        $obj = $this->obj;
        $this->setExpectedException('\InvalidArgumentException');
        unset($obj[0]);
    }
}
