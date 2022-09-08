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

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function testHasItems()
    {
        $obj = $this->obj;
        $this->assertFalse($this->obj->hasItems());

        $this->obj->setItems([
            'test' => []
        ]);

        $this->assertTrue($this->obj->hasItems());
    }

    /**
     * @return void
     */
    public function testNumItems()
    {
        $obj = $this->obj;
        $this->assertEquals(0, $obj->numItems());

        $obj->setItems([
            'test'   => [],
            'foobar' => []
        ]);

         $this->assertEquals(2, $obj->numItems());
    }

    /**
     * @return void
     */
    public function testItems()
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

    /**
     * @return void
     */
    public function testItemCallback()
    {
        $cb = function($item) {
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

    /**
     * @return void
     */
    public function testItemsPriority()
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
