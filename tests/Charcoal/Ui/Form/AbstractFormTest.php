<?php

namespace Charcoal\Tests\Ui\Form;

use Charcoal\Ui\Form\AbstractForm;
use Charcoal\Ui\ServiceProvider\FormServiceProvider;
use Charcoal\Ui\ServiceProvider\LayoutServiceProvider;

/**
 *
 */
class AbstractFormTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\Ui\ContainerIntegrationTrait;

    /**
     * @var AbstractViewClass $obj
     */
    public $obj;

    /**
     *
     */
    public function setUp()
    {
        $container = $this->getContainer();
        $container->register(new FormServiceProvider());
        $container->register(new LayoutServiceProvider());

        $container['view'] = null;

        $this->obj = $this->getMockForAbstractClass(AbstractForm::class, [[
            'logger'             => $container['logger'],
            'layout_builder'     => $container['layout/builder'],
            'form_group_factory' => $container['form/group/factory'],
            'container'          => $container
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

    public function testSetL10nMode()
    {
        $ret = $this->obj->setL10nMode('loop');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('loop', $this->obj->l10nMode());
    }

    public function testSetGroup()
    {
        $obj = $this->obj;
        $ret = $obj->setGroups([
            'test' => []
        ]);
        $this->assertSame($ret, $obj);

        $this->assertEquals(1, count($obj->groups()));
    }

    public function testHasGroups()
    {
        $obj = $this->obj;
        $this->assertFalse($obj->hasGroups());

        $ret = $obj->setGroups([
            'test' => []
        ]);

        $this->assertTrue($obj->hasGroups());
    }

    public function testNumGroups()
    {
        $obj = $this->obj;
        $this->assertEquals(0, $obj->numGroups());

        $ret = $obj->setGroups([
            'test'   => [],
            'foobar' => []
        ]);

         $this->assertEquals(2, $obj->numGroups());
    }

    public function testSetFormData()
    {
        $this->assertEquals([], $this->obj->formData());
        $ret = $this->obj->setFormData([ 'foo' => 'bar' ]);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals([ 'foo' => 'bar' ], $this->obj->formData());

        $this->obj->setFormData([ 'baz' => 42 ]);
        $this->assertEquals([ 'baz' => 42 ], $this->obj->formData());
    }

    public function testAddData()
    {
        $ret = $this->obj->addFormData('foo', 'bar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals([ 'foo' => 'bar' ], $this->obj->formData());
        $this->obj->addFormData('baz', 42);
        $this->assertEquals([ 'foo' => 'bar', 'baz' => 42], $this->obj->formData());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->addFormData(false, 'bar');
    }
}
