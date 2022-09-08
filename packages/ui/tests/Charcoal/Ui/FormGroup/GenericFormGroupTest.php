<?php

namespace Charcoal\Tests\Ui;

// From 'charcoal-ui'
use Charcoal\Ui\Form\GenericForm;
use Charcoal\Ui\FormGroup\GenericFormGroup;
use Charcoal\Ui\ServiceProvider\FormServiceProvider;
use Charcoal\Ui\ServiceProvider\LayoutServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class GenericFormGroupTest extends AbstractTestCase
{
    use \Charcoal\Tests\Ui\ContainerIntegrationTrait;

    /**
     * @var AbstractViewClass $obj
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

        $form = new GenericForm([
            'logger'             => $container['logger'],
            'view'               => $container['view'],
            'layout_builder'     => $container['layout/builder'],
            'form_group_factory' => $container['form/group/factory'],
        ]);

        $this->obj = new GenericFormGroup([
            'form'               => $form,
            'logger'             => $container['logger'],
            'view'               => $container['view'],
            'layout_builder'     => $container['layout/builder'],
            'form_input_builder' => $container['form/input/builder'],
        ]);
    }

    /**
     * @return void
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(GenericFormGroup::class, $this->obj);
    }
}
