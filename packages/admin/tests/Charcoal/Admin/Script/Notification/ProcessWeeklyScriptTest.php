<?php

namespace Charcoal\Tests\Admin\Script\Notification;

use DateTime;
use ReflectionClass;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\Script\Notification\ProcessWeeklyScript;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class ProcessWeeklyScriptTest extends AbstractTestCase
{
    use ReflectionsTrait;

    private \Pimple\Container $container;

    /**
     * Instance of class under test
     * @var CreateScript
     */
    private \Charcoal\Admin\Script\Notification\ProcessWeeklyScript $obj;

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

        $this->obj = new ProcessWeeklyScript([
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
        $this->assertEquals('weekly', $this->callMethod($this->obj, 'frequency'));
    }
}
