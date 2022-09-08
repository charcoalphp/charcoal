<?php

namespace Charcoal\Tests\Ui\Form;

// From 'charcoal-ui'
use Charcoal\Ui\FormGroup\AbstractFormGroup;
use Charcoal\Ui\ServiceProvider\FormServiceProvider;
use Charcoal\Ui\ServiceProvider\LayoutServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class AbstractFormGroupTest extends AbstractTestCase
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

        $form = $container['form/builder']->build([
            'type' => null
        ]);

        $this->obj = $this->getMockForAbstractClass(AbstractFormGroup::class, [
            [
                'form'               => $form,
                'logger'             => $container['logger'],
                'view'               => $container['view'],
                'layout_builder'     => $container['layout/builder'],
                'form_input_builder' => $container['form/input/builder'],
            ],
        ]);
    }

    /**
     * @return void
     */
    public function testSetInputCallback()
    {
        $obj = $this->obj;
        $cb = function($o) {
            return 'foo';
        };
        $ret = $obj->setInputCallback($cb);
        $this->assertSame($ret, $obj);
    }

    /**
     * @return void
     */
    public function testSetInputs()
    {
        $obj = $this->obj;
        $ret = $obj->setInputs([
            'test' => []
        ]);
        $this->assertSame($ret, $obj);
    }

    /**
     * @return void
     */
    public function testSetPriority()
    {
        $this->assertEquals(0, $this->obj->priority());

        $ret = $this->obj->setPriority(42);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(42, $this->obj->priority());

        $this->assertEquals(12, $this->obj->setPriority(12.34)->priority());

        $this->expectException('\InvalidArgumentException');
        $this->obj->setPriority('foobar');
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
    public function testHasInputs()
    {
        $obj = $this->obj;
        $this->assertFalse($obj->hasInputs());

        $ret = $obj->setInputs([
            'test' => []
        ]);

        $this->assertTrue($obj->hasInputs());
    }

    /**
     * @return void
     */
    public function testNumInput()
    {
        $obj = $this->obj;
        $this->assertEquals(0, $obj->numInputs());

        $ret = $obj->setInputs([
            'test'   => [],
            'foobar' => []
        ]);

         $this->assertEquals(2, $obj->numInputs());
    }
}
