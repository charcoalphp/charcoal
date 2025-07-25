<?php

namespace Charcoal\Tests\Ui;

// From 'charcoal-ui'
use Charcoal\Ui\Form\GenericForm;
use Charcoal\Ui\ServiceProvider\FormServiceProvider;
use Charcoal\Ui\ServiceProvider\LayoutServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class GenericFormTest extends AbstractTestCase
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
        (new FormServiceProvider())->register($container);
        (new LayoutServiceProvider())->register($container);

        $this->obj = new GenericForm([
            'logger'             => $container->get('logger'),
            'view'               => $container->get('view'),
            'layout_builder'     => $container->get('layout/builder'),
            'form_group_factory' => $container->get('form/group/factory'),
        ]);
    }

    /**
     * @return void
     */
    public function testType()
    {
        $this->assertEquals('charcoal/ui/form/generic', $this->obj->type());
    }
}
