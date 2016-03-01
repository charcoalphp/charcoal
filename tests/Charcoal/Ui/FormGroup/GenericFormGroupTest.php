<?php

namespace Charcoal\Tests\Ui;

use \Charcoal\Ui\Form\GenericForm;
use \Charcoal\Ui\FormGroup\GenericFormGroup;

class GenericFormGroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractViewClass $obj
     */
    public $obj;

    /**
     * @var \Psr\Log\NullLogger $logger
     */
    public $logger;

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

        $form = new GenericForm([
            'logger'             => $container['logger'],
            'form_group_builder' => $container['form/group/builder'],
            'layout_builder'     => $container['layout/builder']
        ]);

        $this->obj = new GenericFormGroup([
            'form'               => $form,
            'logger'             => $container['logger'],
            'form_input_builder' => $container['form/input/builder'],
            'layout_builder'     => $container['layout/builder']
        ]);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf('\Charcoal\Ui\FormGroup\GenericFormGroup', $this->obj);
    }
}
