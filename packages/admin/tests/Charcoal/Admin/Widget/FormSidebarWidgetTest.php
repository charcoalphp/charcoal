<?php

namespace Charcoal\Tests\Admin\Widget;

use DI\Container;
// From 'charcoal-admin'
use Charcoal\Admin\Widget\FormSidebarWidget;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class FormSidebarWidgetTest extends AbstractTestCase
{
    /**
     * @return void
     */
    public function setUp(): void
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerWidgetDependencies($container);

        $container->set('property/input/factory', $container->get('property/factory'));
        $container->set('property/display/factory', $container->get('property/factory'));

        $this->obj = new FormSidebarWidget([
            'logger' => $container->get('logger'),
            'container' => $container
        ]);
    }

    /**
     * @return void
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(FormSidebarWidget::class, $this->obj);
    }
}
