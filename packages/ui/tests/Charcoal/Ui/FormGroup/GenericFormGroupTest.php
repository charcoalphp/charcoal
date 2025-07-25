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
        (new FormServiceProvider())->register($container);
        (new LayoutServiceProvider())->register($container);

        $form = new GenericForm([
            'logger'             => $container->get('logger'),
            'view'               => $container->get('view'),
            'layout_builder'     => $container->get('layout/builder'),
            'form_group_factory' => $container->get('form/group/factory'),
        ]);

        $this->obj = new GenericFormGroup([
            'form'               => $form,
            'logger'             => $container->get('logger'),
            'view'               => $container->get('view'),
            'layout_builder'     => $container->get('layout/builder'),
            'form_input_builder' => $container->get('form/input/builder'),
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
