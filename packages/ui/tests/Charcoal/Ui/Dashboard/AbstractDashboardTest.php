<?php

namespace Charcoal\Tests\Ui\Dashboard;

// From 'charcoal-ui'
use Charcoal\Ui\UiItemInterface;
use Charcoal\Ui\Dashboard\AbstractDashboard;
use Charcoal\Ui\DashboardGroup\DashboardGroupBuilder;
use Charcoal\Ui\ServiceProvider\DashboardServiceProvider;
use Charcoal\Ui\ServiceProvider\LayoutServiceProvider;
use Charcoal\Ui\ServiceProvider\FormServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class AbstractDashboardTest extends AbstractTestCase
{
    use \Charcoal\Tests\Ui\ContainerIntegrationTrait;

    /**
     * @var AbstractViewClass
     */
    public $obj;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $container = $this->getContainer();
        $container->register(new DashboardServiceProvider());
        $container->register(new LayoutServiceProvider());
        $container->register(new FormServiceProvider());

        $this->obj = $this->getMockForAbstractClass(AbstractDashboard::class, [
            [
                'logger'         => $container['logger'],
                'view'           => $container['view'],
                'layout_builder' => $container['layout/builder'],
                'widget_builder' => $container['form/builder'],
            ],
        ]);
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

    /**
     * @return void
     */
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
     *
     * @return void
     */
    public function testSetLayout()
    {
        $container = $this->getContainer();

        $obj = $this->obj;
        $this->assertNull($obj->layout());

        $exampleLayout = $this->exampleLayout();
        $layout = $container['layout/builder']->build($exampleLayout);

        $ret = $obj->setLayout($layout);
        $this->assertSame($ret, $obj);
        $this->assertSame($layout, $obj->layout());

        $obj->setLayout($exampleLayout);
        $this->assertEquals($layout, $obj->layout());

        $this->expectException('\InvalidArgumentException');
        $obj->setLayout('foobar');
    }

    /**
     * @return void
     */
    public function testSetWidgets()
    {
        $obj = $this->obj;
        $ret = $obj->setWidgets([
            'test'=>[]
        ]);
        $this->assertSame($ret, $obj);
    }

    /**
     * @return void
     */
    public function testAddWidgetInvalidIdentThrowsException()
    {
        $obj = $this->obj;

        $this->expectException('\InvalidArgumentException');
        $obj->addWidget([], []);
    }

    /**
     * @return void
     */
    public function testAddWidgetInvalidWidgetThrowsException()
    {
        $obj = $this->obj;

        $this->expectException('\InvalidArgumentException');
        $obj->addWidget('foo', false);
    }

    /**
     * @return void
     */
    public function testWidgets()
    {
        $obj = $this->obj;

        $ret = $obj->setWidgets([
            'test' => []
        ]);


        $widgets = $obj->widgets();
        $num = 0;
        foreach ($widgets as $w) {
            $this->assertInstanceOf(UiItemInterface::class, $w);
        }
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function testHasWidgets()
    {
        $obj = $this->obj;
        $this->assertFalse($obj->hasWidgets());

        $ret = $obj->setWidgets([
            'test'=>[]
        ]);

        $this->assertTrue($obj->hasWidgets());
    }

    /**
     * @return void
     */
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
