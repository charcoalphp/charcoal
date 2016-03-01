<?php

namespace Charcoal\Tests\Ui\Form;

use \Charcoal\Ui\Form\GenericForm;
use \Charcoal\Ui\FormGroup\FormGroupBuilder;

/**
 *
 */
class AbstractFormTest extends \PHPUnit_Framework_TestCase
{
    public $container;

    /**
     * @var AbstractViewClass $obj
     */
    public $obj;

    /**
     *
     */
    public function setUp()
    {
        $container = new \Pimple\Container();
        $container->register(new \Charcoal\Ui\ServiceProvider\FormServiceProvider());
        $container->register(new \Charcoal\Ui\ServiceProvider\LayoutServiceProvider());

        $container['logger'] = new \Psr\Log\NullLogger();
        $container['view'] = null;

        $this->container = $container;


        $this->logger = new \Psr\Log\NullLogger();
//        $this->obj = new GenericForm([
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Ui\Form\AbstractForm', [[
            'logger'             => $container['logger'],
            'layout_builder'     => $container['layout/builder'],
            'form_group_builder' => $container['form/group/builder']
        ]]);
    }

    public function testSetGroupCallback()
    {
        $obj = $this->obj;
        $cb = function($o) {
            return 'foo';

        };
        $ret = $obj->setGroupCallback($cb);
        $this->assertSame($ret, $obj);
    }

    public function testSetAction()
    {
        $obj = $this->obj;
        $this->assertEquals('', $obj->action());
        $ret = $obj->setAction('foo/bar');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo/bar', $obj->action());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setAction(false);
    }

    public function testSetMethod()
    {
        $obj = $this->obj;
        //$this->assertEquals('post', $obj->method());
        $ret = $obj->setMethod('get');
        $this->assertSame($ret, $obj);
        //$this->assertEquals('get', $obj->method());

        $obj->setMethod('POST');
        //$this->assertEquals('post', $obj->method());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setMethod('foobar');
    }

    public function testSetGroup()
    {
        $obj = $this->obj;
        $ret = $obj->setGroups([
            'test'=>[]
        ]);
        $this->assertSame($ret, $obj);
    }

    public function testHasGroups()
    {
        $obj = $this->obj;
        $this->assertFalse($obj->hasGroups());

        $ret = $obj->setGroups([
            'test'=>[]
        ]);

        $this->assertTrue($obj->hasGroups());
    }

    public function testNumGroups()
    {
        $obj = $this->obj;
        $this->assertEquals(0, $obj->numGroups());

        $ret = $obj->setGroups([
            'test'=>[],
            'foobar'=>[]
        ]);

         $this->assertEquals(2, $obj->numGroups());
    }
}
