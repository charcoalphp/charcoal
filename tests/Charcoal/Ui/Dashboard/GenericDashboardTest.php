<?php

namespace Charcoal\Tests\Ui;

// From 'charcoal-ui'
use Charcoal\Ui\Dashboard\GenericDashboard;
use Charcoal\Ui\ServiceProvider\FormServiceProvider;
use Charcoal\Ui\ServiceProvider\LayoutServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class GenericDashboardTest extends AbstractTestCase
{
    use \Charcoal\Tests\Ui\ContainerIntegrationTrait;

    /**
     * @var GenericDashboard
     */
    public $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $container = $this->getContainer();
        $container->register(new LayoutServiceProvider());
        $container->register(new FormServiceProvider());

        $provider = $this->getContainerProvider();
        $provider->registerView($container);

        $this->obj = new GenericDashboard([
            'logger'         => $container['logger'],
            'view'           => $container['view'],
            'layout_builder' => $container['layout/builder'],
            'widget_builder' => $container['form/builder'],
        ]);
    }

    /**
     * @return void
     */
    public function testType()
    {
        $this->assertEquals('charcoal/ui/dashboard/generic', $this->obj->type());
    }
}
