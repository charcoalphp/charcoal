<?php

namespace Charcoal\Tests\Ui;

use Charcoal\Ui\FormInput\GenericFormInput;
use Charcoal\Ui\ServiceProvider\FormServiceProvider;
use Charcoal\Ui\ServiceProvider\LayoutServiceProvider;

class GenericFormInputTest extends \PHPUnit_Framework_TestCase
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

        $this->obj = new GenericFormInput([
            'logger'             => $container['logger'],
            'layout_builder'     => $container['layout/builder'],
            'form_group_factory' => $container['form/group/factory']
        ]);
    }

    public function testType()
    {
        $this->assertEquals('charcoal/ui/form-input/generic', $this->obj->type());
    }
}
