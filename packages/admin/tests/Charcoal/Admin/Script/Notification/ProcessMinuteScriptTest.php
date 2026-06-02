<?php

namespace Charcoal\Tests\Admin\Script\Notification;

use DateTime;
use ReflectionClass;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\Script\Notification\ProcessMinuteScript;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class ProcessMinuteScriptTest extends AbstractTestCase
{
    use ReflectionsTrait;

    private \Pimple\Container $container;

    /**
     * Instance of class under test
     * @var CreateScript
     */
    private \Charcoal\Admin\Script\Notification\ProcessMinuteScript $obj;

    private function getContainer(): \Pimple\Container
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerScriptDependencies($container);

        $container['email/factory'] = (fn(Container $container): mixed => $container['model/factory']);

        return $container;
    }

    public function setUp(): void
    {
        $this->container = $this->getContainer();

        $this->obj = new ProcessMinuteScript([
            'logger' => $this->container['logger'],
            'climate' => $this->container['climate'],
            'model_factory' => $this->container['model/factory'],

            // Will call `setDependencies()` on object. AdminScript expects a 'mode/factory'.
            'container' => $this->container
        ]);
    }


    public function testDefaultArguments(): void
    {
        $args = $this->obj->defaultArguments();
        $this->assertArrayHasKey('now', $args);
    }

    public function testFrequency(): void
    {
        $this->assertEquals('minute', $this->callMethod($this->obj, 'frequency'));
    }
}
