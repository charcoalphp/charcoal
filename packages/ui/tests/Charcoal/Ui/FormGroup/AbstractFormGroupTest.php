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

    public function testSetInputCallback(): void
    {
        $obj = $this->obj;
        $cb = (fn($o): string => 'foo');
        $ret = $obj->setInputCallback($cb);
        $this->assertSame($ret, $obj);
    }

    public function testSetInputs(): void
    {
        $obj = $this->obj;
        $ret = $obj->setInputs([
            'test' => []
        ]);
        $this->assertSame($ret, $obj);
    }

    public function testSetPriority(): void
    {
        $this->assertEquals(0, $this->obj->priority());

        $ret = $this->obj->setPriority(42);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(42, $this->obj->priority());

        $this->assertEquals(12, $this->obj->setPriority(12.34)->priority());

        $this->expectException('\InvalidArgumentException');
        $this->obj->setPriority('foobar');
    }

    public function testSetL10nMode(): void
    {
        $ret = $this->obj->setL10nMode('loop');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('loop', $this->obj->l10nMode());
    }

    public function testHasInputs(): void
    {
        $obj = $this->obj;
        $this->assertFalse($obj->hasInputs());

        $obj->setInputs([
            'test' => []
        ]);

        $this->assertTrue($obj->hasInputs());
    }

    public function testNumInput(): void
    {
        $obj = $this->obj;
        $this->assertEquals(0, $obj->numInputs());

        $obj->setInputs([
            'test'   => [],
            'foobar' => []
        ]);

         $this->assertEquals(2, $obj->numInputs());
    }
}
