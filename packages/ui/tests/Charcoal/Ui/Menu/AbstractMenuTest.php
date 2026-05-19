<?php

namespace Charcoal\Tests\Ui\Layout;

// From 'charcoal-ui'
use Charcoal\Ui\Menu\AbstractMenu;
use Charcoal\Ui\MenuItem\MenuItemInterface;
use Charcoal\Ui\ServiceProvider\MenuServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class AbstractMenuTest extends AbstractTestCase
{
    use \Charcoal\Tests\Ui\ContainerIntegrationTrait;

    /**
     * @var AbstractMenu
     */
    public $obj;

    protected function setUp(): void
    {
        $container = $this->getContainer();
        $container->register(new MenuServiceProvider());

        $this->obj = $this->getMockForAbstractClass(AbstractMenu::class, [
            [
                'container'         => $container,
                'logger'            => $container['logger'],
                'view'              => $container['view'],
                'menu_item_builder' => $container['menu/item/builder'],
            ],
        ]);
    }

    public function testHasItems(): void
    {
        $this->assertFalse($this->obj->hasItems());

        $this->obj->setItems([
            'test' => []
        ]);

        $this->assertTrue($this->obj->hasItems());
    }

    public function testNumItems(): void
    {
        $obj = $this->obj;
        $this->assertEquals(0, $obj->numItems());

        $obj->setItems([
            'test'   => [],
            'foobar' => []
        ]);

         $this->assertEquals(2, $obj->numItems());
    }

    public function testItems(): void
    {
        $ret = iterator_to_array($this->obj->items());
        $this->assertEmpty($ret);

        $items = [
            'test'   => [],
            'foobar' => []
        ];
        $this->obj->setItems($items);

        $ret = iterator_to_array($this->obj->items());

        $this->assertArrayHasKey('test', $ret);
        $this->assertArrayHasKey('foobar', $ret);

        $this->assertInstanceOf(MenuItemInterface::class, $ret['test']);
        $this->assertInstanceOf(MenuItemInterface::class, $ret['foobar']);
    }

    public function testItemCallback(): void
    {
        $cb = function(array $item): void {
            $item['property_from_callback'] = 'yes';
        };
        $ret = $this->obj->setItemCallback($cb);
        $this->assertSame($ret, $this->obj);

        $this->obj->setItems([
            'test'   => [],
            'foobar' => []
        ]);

        $ret = iterator_to_array($this->obj->items());
        $this->assertEquals('yes', $ret['test']['property_from_callback']);
        $this->assertEquals('yes', $ret['foobar']['property_from_callback']);
    }

    public function testItemsPriority(): void
    {
        $ret = iterator_to_array($this->obj->items());
        $this->assertEmpty($ret);

        $items = [
            'test'   => [
                'priority' => 2
            ],
            'foobar' => [
                'priority' => 1
            ]
        ];
        $this->obj->setItems($items);

        $ret = iterator_to_array($this->obj->items());

        $this->assertArrayHasKey('test', $ret);
        $this->assertArrayHasKey('foobar', $ret);
    }
}
