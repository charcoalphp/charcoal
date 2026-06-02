<?php
namespace Charcoal\Tests\Admin\Template\Object;

use ReflectionClass;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\Template\Object\CreateTemplate;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class CreateTemplateTest extends AbstractTestCase
{
    use ReflectionsTrait;

    /**
     * Tested Class.
     */
    private \Charcoal\Admin\Template\Object\CreateTemplate $obj;

    /**
     * Store the service container.
     */
    private ?\Pimple\Container $container = null;

    /**
     * Set up the test.
     */
    public function setUp(): void
    {
        $container = $this->container();

        $this->obj = new CreateTemplate([
            'logger'          => $container['logger'],
            'metadata_loader' => $container['metadata/loader'],
            'container'       => $container
        ]);
        //$this->obj->setDependencies($container);
    }

    public function testAuthRequiredIsTrue(): void
    {
        $res = $this->callMethod($this->obj, 'authRequired');
        $this->assertTrue($res);
    }

    public function testTitle(): void
    {
        $this->obj->setObjType('charcoal/admin/user');
        $ret = $this->obj->title();
        $ret2 = $this->obj->title();

        $this->assertSame($ret, $ret2);
    }

    /**
     * Set up the service container.
     */
    protected function container(): \Pimple\Container
    {
        if (!$this->container instanceof \Pimple\Container) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerTemplateDependencies($container);
            $containerProvider->registerWidgetFactory($container);
            $containerProvider->registerDashboardBuilder($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
