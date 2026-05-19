<?php

namespace Charcoal\Tests\Admin\Widget;

// From Pimple
use Pimple\Container;

// From Slim
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

// From 'charcoal-admin'
use Charcoal\Admin\Widget\SecondaryMenuWidget;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 * Class SecondaryMenuWidgetTest
 */
class SecondaryMenuWidgetTest extends AbstractTestCase
{
    /**
     * @var SecondaryMenuWidget
     */
    public $obj;

    public function setUp(): void
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerWidgetDependencies($container);
        $containerProvider->registerWidgetFactory($container);

        $container['request'] = Request::createFromEnvironment(Environment::mock());
        $container['secondary-menu/group/factory'] = $container['widget/factory'];

        $this->obj = new SecondaryMenuWidget([
            'logger' => $container['logger'],
            'container' => $container
        ]);
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(SecondaryMenuWidget::class, $this->obj);
    }
}
