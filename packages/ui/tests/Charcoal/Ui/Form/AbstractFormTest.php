<?php

namespace Charcoal\Tests\Ui\Form;

// From 'charcoal-ui'
use Charcoal\Ui\Form\AbstractForm;
use Charcoal\Ui\ServiceProvider\FormServiceProvider;
use Charcoal\Ui\ServiceProvider\LayoutServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class AbstractFormTest extends AbstractTestCase
{
    use \Charcoal\Tests\Ui\ContainerIntegrationTrait;

    /**
     * @var AbstractViewClass
     */
    public $obj;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $container = $this->getContainer();
        $container->register(new FormServiceProvider());
        $container->register(new LayoutServiceProvider());

        $this->obj = $this->getMockForAbstractClass(AbstractForm::class, [
            [
                'container'          => $container,
                'logger'             => $container['logger'],
                'view'               => $container['view'],
                'layout_builder'     => $container['layout/builder'],
                'form_group_factory' => $container['form/group/factory'],
            ],
        ]);
    }

    /**
     * @return void
     */
    public function testSetGroupCallback()
    {
        $cb = function($o) {
            return 'foo';
        };
        $ret = $this->obj->setGroupCallback($cb);
        $this->assertSame($ret, $this->obj);
    }

    /**
     * @return void
     */
    public function testSetAction()
    {
        $this->assertEquals('', $this->obj->action());
        $ret = $this->obj->setAction('foo/bar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo/bar', $this->obj->action());

        $this->expectException('\InvalidArgumentException');
        $this->obj->setAction(false);
    }

    /**
     * @return void
     */
    public function testSetMethod()
    {
        //$this->assertEquals('post', $obj->method());
        $ret = $this->obj->setMethod('get');
        $this->assertSame($ret, $this->obj);
        //$this->assertEquals('get', $obj->method());

        $this->obj->setMethod('POST');
        //$this->assertEquals('post', $obj->method());

        $this->expectException('\InvalidArgumentException');
        $this->obj->setMethod('foobar');
    }

    /**
     * @return void
     */
    public function testSetL10nMode()
    {
        $ret = $this->obj->setL10nMode('loop');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('loop', $this->obj->l10nMode());
    }

    /**
     * @return void
     */
    public function testSetGroup()
    {
        $ret = $this->obj->setGroups([
            'test' => []
        ]);
        $this->assertSame($ret, $this->obj);

        $this->assertEquals(1, $this->obj->numGroups());
    }

    /**
     * @return void
     */
    public function testAddGroup()
    {
        $ret = $this->obj->addGroup('ident', []);
        $this->assertSame($ret, $this->obj);
    }

    /**
     * @return void
     */
    public function testHasGroups()
    {
        $this->assertFalse($this->obj->hasGroups());

        $ret = $this->obj->setGroups([
            'test' => []
        ]);

        $this->assertTrue($this->obj->hasGroups());
    }

    /**
     * @return void
     */
    public function testNumGroups()
    {
        $this->assertEquals(0, $this->obj->numGroups());

        $ret = $this->obj->setGroups([
            'test'   => [],
            'foobar' => []
        ]);

         $this->assertEquals(2, $this->obj->numGroups());
    }

    /**
     * @return void
     */
    public function testSetFormData()
    {
        $this->assertEquals([], $this->obj->formData());
        $ret = $this->obj->setFormData([ 'foo' => 'bar' ]);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals([ 'foo' => 'bar' ], $this->obj->formData());

        $this->obj->setFormData([ 'baz' => 42 ]);
        $this->assertEquals([ 'baz' => 42 ], $this->obj->formData());
    }

    /**
     * @return void
     */
    public function testAddData()
    {
        $ret = $this->obj->addFormData('foo', 'bar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals([ 'foo' => 'bar' ], $this->obj->formData());
        $this->obj->addFormData('baz', 42);
        $this->assertEquals([ 'foo' => 'bar', 'baz' => 42], $this->obj->formData());

        $this->expectException('\InvalidArgumentException');
        $this->obj->addFormData(false, 'bar');
    }
}
