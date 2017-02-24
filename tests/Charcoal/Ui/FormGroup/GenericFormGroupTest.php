<?php

namespace Charcoal\Tests\Ui;

use Charcoal\Ui\Form\GenericForm;
use Charcoal\Ui\FormGroup\GenericFormGroup;
use Charcoal\Ui\ServiceProvider\FormServiceProvider;
use Charcoal\Ui\ServiceProvider\LayoutServiceProvider;

class GenericFormGroupTest extends \PHPUnit_Framework_TestCase
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

        $form = new GenericForm([
            'logger'             => $container['logger'],
            'layout_builder'     => $container['layout/builder'],
            'form_group_factory' => $container['form/group/factory']
        ]);

        $this->obj = new GenericFormGroup([
            'form'               => $form,
            'logger'             => $container['logger'],
            'layout_builder'     => $container['layout/builder'],
            'form_input_builder' => $container['form/input/builder']
        ]);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(GenericFormGroup::class, $this->obj);
    }
}
