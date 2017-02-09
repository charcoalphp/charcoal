<?php

namespace Charcoal\Tests\Ui\Dashboard;

// PSR-3 logger test dependencies
use \Psr\Log\NullLogger;

// Pimple test dependencies
use \Pimple\Container;

// Tested module (`charcoal-ui`) test dependencies
use \Charcoal\Ui\UiItemInterface;
use \Charcoal\Ui\Dashboard\GenericDashboard;
use \Charcoal\Ui\DashboardGroup\DashboardGroupBuilder;
use \Charcoal\Ui\ServiceProvider\DashboardServiceProvider;
use \Charcoal\Ui\ServiceProvider\LayoutServiceProvider;
use \Charcoal\Ui\ServiceProvider\FormServiceProvider;

class AbstractDashboardTest extends \PHPUnit_Framework_TestCase
{
    public $container;

    /**
     * @var AbstractViewClass $obj
     */
    public $obj;

    /**
     * @var \Psr\Log\NullLogger $logger
     */
    public $logger;

    /**
     *
     */
    public function setUp()
    {
        $container = new Container();
        $container->register(new DashboardServiceProvider());
        $container->register(new LayoutServiceProvider());
        $container->register(new FormServiceProvider());

        // Fulfills the services dependencies on `logger` and `view`.
        $container['logger'] = new NullLogger();
        $container['view'] = null;


        $this->container = $container;

        $this->obj = $this->getMockForAbstractClass('\Charcoal\Ui\Dashboard\AbstractDashboard', [[
            'logger'         => $container['logger'],
            'layout_builder' => $container['layout/builder'],
            'widget_builder' => $container['form/builder']
        ]]);
    }

    /**
     * Helper method, example layout for tests.
     *
     * @return array
     */
    protected function exampleLayout()
    {
        return [
            'structure' => [
                'columns' => [1,1,1]
            ]
        ];
    }

    public function testSetWidgetCallback()
    {
        $obj = $this->obj;
        $cb = function($o) {
            return 'foo';
        };
        $ret = $obj->setWidgetCallback($cb);
        $this->assertSame($ret, $obj);
    }

    /**
     * Assert that
     * - `layout()` is null by default
     * - `etLayout()` is chainable
     * - calling `setLayout()` with an Layout objects set the layout
     * - calling `setLayout()` with an array sets the same layout
     * - `setLayout()` throws an exception if not an array / Layout object
     */
    public function testSetLayout()
    {
        $obj = $this->obj;
        $this->assertNull($obj->layout());

        $exampleLayout = $this->exampleLayout();
        $layout = $this->container['layout/builder']->build($exampleLayout);

        $ret = $obj->setLayout($layout);
        $this->assertSame($ret, $obj);
        $this->assertSame($layout, $obj->layout());

        $obj->setLayout($exampleLayout);
        $this->assertEquals($layout, $obj->layout());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setLayout('foobar');
    }

    public function testSetWidgets()
    {
        $obj = $this->obj;
        $ret = $obj->setWidgets([
            'test'=>[]
        ]);
        $this->assertSame($ret, $obj);
    }

    public function testAddWidgetInvalidIdentThrowsException()
    {
        $obj = $this->obj;

        $this->setExpectedException('\InvalidArgumentException');
        $obj->addWidget([], []);
    }

    public function testAddWidgetInvalidWidgetThrowsException()
    {
        $obj = $this->obj;

        $this->setExpectedException('\InvalidArgumentException');
        $obj->addWidget('foo', false);
    }

    public function testWidgets()
    {
        $obj = $this->obj;

        $ret = $obj->setWidgets([
            'test' => []
        ]);


        $widgets = $obj->widgets();
        $num = 0;
        foreach ($widgets as $w) {
            $this->assertInstanceOf('\Charcoal\Ui\UiItemInterface', $w);
        }
    }

    public function testWidgetsCallback()
    {
        $obj = $this->obj;
        $obj->setWidgets([
            'test' => []
        ]);

        $cb = function(UiItemInterface $widget) {
            $widget['foo'] = 'bar';
        };

        $widgets = $obj->widgets($cb);

        foreach ($widgets as $w) {
            $this->assertEquals($w['foo'], 'bar');
        }
    }

    public function testHasWidgets()
    {
        $obj = $this->obj;
        $this->assertFalse($obj->hasWidgets());

        $ret = $obj->setWidgets([
            'test'=>[]
        ]);

        $this->assertTrue($obj->hasWidgets());
    }

    public function testNumWidgets()
    {
        $obj = $this->obj;
        $this->assertEquals(0, $obj->numWidgets());

        $ret = $obj->setWidgets([
            'test'=>[],
            'foobar'=>[]
        ]);

         $this->assertEquals(2, $obj->numWidgets());
    }
}
