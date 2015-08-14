<?php

namespace Charcoal\Tests\Core;

use \Charcoal\Core\AbstractFactory as AbstractFactory;

use \Charcoal\Tests\Core\AbstractFactoryClass as AbstractFactoryClass;

/**
*
*/
class AbstractFactoryTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    /**
    *
    */
    public function setUp()
    {
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Core\AbstractFactory');
    }

    public function testSetBaseClass()
    {
        $obj = $this->obj;
        $ret = $obj->set_base_class('\Charcoal\Model\AbstractModel');
        $this->assertSame($ret, $obj);
    }

    // /**
    // *
    // */
    // public function testInstanceDifferentClass()
    // {
    //     $obj1 = \Charcoal\Model\ModelFactory::instance();
    //     $obj2 = \Charcoal\Property\PropertyFactory::instance();
    //     $this->assertNotSame($obj1, $obj2);
    // }

    // /**
    // *
    // */
    // public function testSetFactoryMode()
    // {
    //     $obj = $this->obj;
    //     $this->assertEquals(AbstractFactory::MODE_CLASS_MAP, $obj->factory_mode());

    //     $ret = $obj->set_factory_mode(AbstractFactory::MODE_IDENT);
    //     $this->assertSame($ret, $obj);
    //     $this->assertEquals(AbstractFactory::MODE_IDENT, $obj->factory_mode());

    //     $this->setExpectedException('\InvalidArgumentException');
    //     $obj->set_factory_mode('foobarbaz');
    // }

    // /**
    // *
    // */
    // public function testCreate()
    // {
    //     $obj = $this->obj;

    //     $obj->add_class('test', '\Charcoal\Tests\Core\AbstractFactoryClass');
    //     $item = $obj->create('test');
    //     $this->assertInstanceOf('\Charcoal\Tests\Core\AbstractFactoryClass', $item);

    //     // Make sure a NEW object is created everytime, when using create
    //     $item2 = $obj->create('test');
    //     $this->assertNotSame($item, $item2);

    //     $this->setExpectedException('\InvalidArgumentException');
    //     $obj->create(false);
    // }

    // /**
    // *
    // */
    // public function testGet()
    // {
    //     $obj = $this->obj;

    //     $obj->add_class('test', '\Charcoal\Tests\Core\AbstractFactoryClass');
    //     $item = $obj->get('test');
    //     $this->assertInstanceOf('\Charcoal\Tests\Core\AbstractFactoryClass', $item);

    //     // Make sure the last created instance is used, when using get
    //     $item2 = $obj->get('test');
    //     $this->assertSame($item, $item2);

    //     $item3 = $obj->create('test');
    //     $item4 =$obj->get('test');
    //     $this->assertSame($item3, $item4);
    //     $this->assertNotSame($item2, $item4);
    // }

    // /**
    // *
    // */
    // public function testGetInvalidTypeThrowsException()
    // {
    //     $this->setExpectedException('\InvalidArgumentException');
    //     $this->obj->get('foo');
    // }

    // /**
    // *
    // */
    // public function testGetInvalidParameterThrowsException()
    // {
    //     $this->setExpectedException('\InvalidArgumentException');
    //     $this->obj->get(false);
    // }

    // /**
    // *
    // */
    // public function testTypeToClassnameClassmap()
    // {
    //     $obj = $this->obj;
    //     $obj->set_factory_mode(AbstractFactory::MODE_CLASS_MAP);

    //     $obj->add_class('foo', '\Charcoal\Tests\Core\AbstractFactoryClass');
    //     $this->assertEquals('\Charcoal\Tests\Core\AbstractFactoryClass', $obj->type_to_classname('foo'));

    //     $this->setExpectedException('\InvalidArgumentException');
    //     $ret = $obj->type_to_classname('error');

    // }

    // /**
    // * @dataProvider providerIdentClassname
    // */
    // public function testTypeToClassnameIdent($ident, $classname)
    // {
    //     $obj = $this->obj;
    //     $obj->set_factory_mode(AbstractFactory::MODE_IDENT);

    //     $this->assertEquals($classname, $obj->type_to_classname($ident));
    // }

    // /**
    // * @dataProvider providerIdentClassname
    // */
    // public function testIdentToClassname($ident, $classname)
    // {
    //     $obj = $this->obj;
    //     $this->assertEquals($classname, $obj->ident_to_classname($ident));
    // }

    // /**
    // *
    // */
    // public function providerIdentClassName()
    // {
    //     return [
    //         ['foo/bar', '\Foo\Bar'],
    //         ['/foo/bar', '\Foo\Bar'],
    //         ['foo/bar/', '\Foo\Bar'],
    //         ['foo/bar-baz', '\Foo\BarBaz']
    //     ];
    // }

    // /**
    // *
    // */
    // public function testIsTypeAvailableClassmap()
    // {
    //     $obj = $this->obj;
    //     $obj->set_factory_mode(AbstractFactory::MODE_CLASS_MAP);
    //     $this->assertFalse($obj->is_type_available('foobarbar'));

    //     $obj->set_class_map([
    //         'foo'=>'\Charcoal\Tests\Core\AbstractFactoryClass',
    //     ]);

    //     $this->assertTrue($obj->is_type_available('foo'));
    //     $this->assertFalse($obj->is_type_available('bar'));
    // }

    // /**
    // *
    // */
    // public function testIsTypeAvailableIdent()
    // {
    //     $obj = $this->obj;
    //     $obj->set_factory_mode(AbstractFactory::MODE_IDENT);
    //     $this->assertFalse($obj->is_type_available('foobar'));

    //     $this->assertTrue($obj->is_type_available('charcoal/tests/core/abstract-factory-class'));
    // }

    // public function testSetClassMap()
    // {
    //     $obj = $this->obj;
    //     $ret = $obj->set_class_map([
    //         'foo'=>'\Charcoal\Tests\Core\AbstractFactoryClass',
    //     ]);

    //     $this->assertSame($ret, $obj);
    //     $this->assertTrue(in_array('foo', array_keys($obj->class_map())));

    //     $this->setExpectedException('\InvalidArgumentException');
    //     $obj->set_class_map([
    //         'bar'=>'\Invalid\Class\Name'
    //     ]);
    // }
}
