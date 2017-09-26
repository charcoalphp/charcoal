<?php

namespace Charcoal\Tests\Ui;

use Charcoal\Ui\Form\GenericForm;
use Charcoal\Ui\ServiceProvider\FormServiceProvider;
use Charcoal\Ui\ServiceProvider\LayoutServiceProvider;

class GenericFormTest extends \PHPUnit_Framework_TestCase
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

        $this->obj = new GenericForm([
            'logger'             => $container['logger'],
            'layout_builder'     => $container['layout/builder'],
            'form_group_factory' => $container['form/group/factory']
        ]);
    }

    public function testType()
    {
        $this->assertEquals('charcoal/ui/form/generic', $this->obj->type());
    }
}
