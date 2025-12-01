<?php

namespace Charcoal\Tests\Admin;

use DI\Container;
// From 'charcoal-admin'
use Charcoal\Admin\Service\Exporter;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Exporter::class)]
class ExporterTest extends AbstractTestCase
{
    /**
     * @var Exporter
     */
    private $obj;

    /**
     * Store the service container.
     *
     * @var Container
     */
    private $container;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $container = $this->container();

        $this->obj = new Exporter([
           'logger'          => $container->get('logger'),
           'factory'         => $container->get('model/factory'),
           'propertyFactory' => $container->get('property/factory'),
           'translator'      => $container->get('translator'),
           'obj_type'        => 'charcoal/admin/user',
           'export_ident'    => 'y',
        ]);
    }

    /**
     * @return void
     */
    public function testExport()
    {
        $this->assertTrue(true);
    }

    /**
     * Set up the service container.
     *
     * @return Container
     */
    protected function container()
    {
        if ($this->container === null) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerBaseServices($container);
            $containerProvider->registerTranslatorServiceProvider($container);
            $containerProvider->registerViewServiceProvider($container);
            $containerProvider->registerModelServiceProvider($container);

            $container->set('view', $this->createMock('\Charcoal\View\ViewInterface'));

            $this->container = $container;
        }

        return $this->container;
    }
}
