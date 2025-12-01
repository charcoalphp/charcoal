<?php

namespace Charcoal\Tests\Admin\Script\Notification;

use DI\Container;
// From 'charcoal-admin'
use Charcoal\Admin\Script\Notification\ProcessDailyScript;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Admin\ContainerProvider;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProcessDailyScript::class)]
class ProcessDailyScriptTest extends AbstractTestCase
{
    use ReflectionsTrait;

    /**
     * @var Container
     */
    private $container;

    /**
     * Instance of class under test
     * @var CreateScript
     */
    private $obj;

    /**
     * @return Container
     */
    private function getContainer()
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerScriptDependencies($container);

        $container->set('email/factory', function (Container $container) {
            return $container->get('model/factory');
        });

        return $container;
    }

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->container = $this->getContainer();

        $this->obj = new ProcessDailyScript([
            'logger' => $this->container['logger'],
            'climate' => $this->container['climate'],
            'model_factory' => $this->container['model/factory'],

            // Will call `setDependencies()` on object. AdminScript expects a 'mode/factory'.
            'container' => $this->container
        ]);
    }


    /**
     * @return void
     */
    public function testDefaultArguments()
    {
        $args = $this->obj->defaultArguments();
        $this->assertArrayHasKey('now', $args);
    }

    /**
     * @return void
     */
    public function testFrequency()
    {
        $this->assertEquals('daily', $this->callMethod($this->obj, 'frequency'));
    }
}
