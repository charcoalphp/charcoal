<?php

namespace Charcoal\Tests\Admin\Widget;

use DI\Container;
use Nyholm\Psr7\ServerRequest;
// From 'charcoal-admin'
use Charcoal\Admin\Widget\SecondaryMenuWidget;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SecondaryMenuWidget::class)]
class SecondaryMenuWidgetTest extends AbstractTestCase
{
    /**
     * @var SecondaryMenuWidget
     */
    public $obj;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerWidgetDependencies($container);
        $containerProvider->registerWidgetFactory($container);

        $container->set('request', $this->createMock(ServerRequest::class));
        $container->set('secondary-menu/group/factory', $container->get('widget/factory'));

        $this->obj = new SecondaryMenuWidget([
            'logger' => $container->get('logger'),
            'container' => $container
        ]);
    }

    /**
     * @return void
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(SecondaryMenuWidget::class, $this->obj);
    }
}
