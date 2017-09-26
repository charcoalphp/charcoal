<?php

namespace Charcoal\Tests\Ui;

use Charcoal\Ui\Dashboard\GenericDashboard;
use Charcoal\Ui\ServiceProvider\FormServiceProvider;
use Charcoal\Ui\ServiceProvider\LayoutServiceProvider;

class GenericDashboardTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\Ui\ContainerIntegrationTrait;

    /**
     * @var GenericDashboard $obj
     */
    public $obj;

    /**
     *
     */
    public function setUp()
    {
        $container = $this->getContainer();
        $container->register(new LayoutServiceProvider());
        $container->register(new FormServiceProvider());

        $container['view'] = null;

        $this->obj =new GenericDashboard([
            'logger'         => $container['logger'],
            'layout_builder' => $container['layout/builder'],
            'widget_builder' => $container['form/builder']
        ]);
    }

    public function testType()
    {
        $this->assertEquals('charcoal/ui/dashboard/generic', $this->obj->type());
    }
}
