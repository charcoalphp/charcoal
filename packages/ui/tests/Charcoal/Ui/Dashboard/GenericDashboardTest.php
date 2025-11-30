<?php

namespace Charcoal\Tests\Ui;

use Charcoal\Ui\Dashboard\GenericDashboard;
use Charcoal\Ui\ServiceProvider\FormServiceProvider;
use Charcoal\Ui\ServiceProvider\LayoutServiceProvider;
use Charcoal\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GenericDashboard::class)]
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
    protected function setUp(): void
    {
        $container = $this->getContainer();
        (new LayoutServiceProvider())->register($container);
        (new FormServiceProvider())->register($container);

        $this->obj = new GenericDashboard([
            'logger'         => $container->get('logger'),
            'view'           => $container->get('view'),
            'layout_builder' => $container->get('layout/builder'),
            'widget_builder' => $container->get('form/builder'),
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
