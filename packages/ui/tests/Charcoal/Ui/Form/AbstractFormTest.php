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

    public function testSetGroupCallback(): void
    {
        $cb = (fn($o): string => 'foo');
        $ret = $this->obj->setGroupCallback($cb);
        $this->assertSame($ret, $this->obj);
    }

    public function testSetAction(): void
    {
        $this->assertEquals('', $this->obj->action());
        $ret = $this->obj->setAction('foo/bar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo/bar', $this->obj->action());

        $this->expectException('\InvalidArgumentException');
        $this->obj->setAction(false);
    }

    public function testSetMethod(): void
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

    public function testSetL10nMode(): void
    {
        $ret = $this->obj->setL10nMode('loop');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('loop', $this->obj->l10nMode());
    }

    public function testSetGroup(): void
    {
        $ret = $this->obj->setGroups([
            'test' => []
        ]);
        $this->assertSame($ret, $this->obj);

        $this->assertEquals(1, $this->obj->numGroups());
    }

    public function testAddGroup(): void
    {
        $ret = $this->obj->addGroup('ident', []);
        $this->assertSame($ret, $this->obj);
    }

    public function testHasGroups(): void
    {
        $this->assertFalse($this->obj->hasGroups());

        $this->obj->setGroups([
            'test' => []
        ]);

        $this->assertTrue($this->obj->hasGroups());
    }

    public function testNumGroups(): void
    {
        $this->assertEquals(0, $this->obj->numGroups());

        $this->obj->setGroups([
            'test'   => [],
            'foobar' => []
        ]);

         $this->assertEquals(2, $this->obj->numGroups());
    }

    public function testSetFormData(): void
    {
        $this->assertEquals([], $this->obj->formData());
        $ret = $this->obj->setFormData([ 'foo' => 'bar' ]);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals([ 'foo' => 'bar' ], $this->obj->formData());

        $this->obj->setFormData([ 'baz' => 42 ]);
        $this->assertEquals([ 'baz' => 42 ], $this->obj->formData());
    }

    public function testAddData(): void
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
