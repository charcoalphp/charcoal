<?php

namespace Charcoal\Tests\Admin\Widget;

use DI\Container;
// From 'charcoal-admin'
use Charcoal\Admin\Widget\FormGroupWidget;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class FormGroupWidgetTest extends AbstractTestCase
{
    /**
     * @return void
     */
    public function setUp(): void
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerWidgetDependencies($container);
        $containerProvider->registerDashboardBuilder($container);
        $containerProvider->registerAuthorizer($container);
        $containerProvider->registerAuthenticator($container);


        $container->set('form/input/builder', $this->createMock(\Charcoal\Ui\FormInput\FormInputBuilder::class, ''));

        $container->set('authorizer', $container->get('admin/authorizer'));
        $container->set('authenticator', $container->get('admin/authenticator'));

        $this->obj = new FormGroupWidget([
            'logger' => $container->get('logger'),
            'container' => $container
        ]);
    }

    /**
     * @return void
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(FormGroupWidget::class, $this->obj);
    }
}
