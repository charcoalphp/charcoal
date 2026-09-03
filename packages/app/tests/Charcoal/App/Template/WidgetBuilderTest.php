<?php

namespace Charcoal\Tests\App\Template;

use InvalidArgumentException;

use Pimple\Container;

use Charcoal\App\Template\WidgetBuilder;
use Charcoal\Factory\FactoryInterface;
use Charcoal\Tests\AbstractTestCase;

class WidgetBuilderTest extends AbstractTestCase
{
    /**
     * @var WidgetBuilder
     */
    private $obj;

    /**
     * @var object
     */
    private $widget;

    public function setUp(): void
    {
        $this->widget = new class () {
            public $type;
            public $data;

            public function setData($data)
            {
                $this->data = $data;
                return $this;
            }
        };

        $factory = $this->createMock(FactoryInterface::class);
        $factory->method('create')->willReturnCallback(function ($type) {
            $this->widget->type = $type;
            return $this->widget;
        });

        $this->obj = new WidgetBuilder($factory, new Container());
    }

    public function testBuildUsesController()
    {
        $widget = $this->obj->build([
            'controller' => 'foo/bar',
            'active'     => true,
        ]);

        $this->assertSame($this->widget, $widget);
        $this->assertEquals('foo/bar', $widget->type);
        $this->assertTrue($widget->data['active']);
    }

    public function testBuildUsesTypeFallback()
    {
        $widget = $this->obj->build([
            'type' => 'baz/qux',
        ]);

        $this->assertEquals('baz/qux', $widget->type);
    }

    public function testBuildRequiresType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->build([
            'active' => true,
        ]);
    }
}
