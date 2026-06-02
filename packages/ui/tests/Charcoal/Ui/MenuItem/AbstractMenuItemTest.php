<?php

namespace Charcoal\Tests\Ui\MenuItem;

// From 'charcoal-ui'
use Charcoal\Ui\MenuItem\AbstractMenuItem;
use Charcoal\Ui\ServiceProvider\MenuServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class AbstractMenuItemTest extends AbstractTestCase
{
    use \Charcoal\Tests\Ui\ContainerIntegrationTrait;

    /**
     * @var AbstractMenuItem
     */
    public $obj;

    protected function setUp(): void
    {
        $container = $this->getContainer();
        $container->register(new MenuServiceProvider());

        $menu = $container['menu/builder']->build([]);

        $this->obj = new class ([
            'menu'              => $menu,
            'logger'            => $container['logger'],
            'view'              => $container['view'],
            'menu_item_builder' => $container['menu/item/builder'],
        ]) extends AbstractMenuItem {};
    }

    public function testHasChildren(): void
    {
        $obj = $this->obj;
        $this->assertFalse($obj->hasChildren());

        $obj->setChildren([
            'test' => []
        ]);

        $this->assertTrue($obj->hasChildren());
    }

    public function testNumChildren(): void
    {
        $obj = $this->obj;
        $this->assertEquals(0, $obj->numChildren());

        $obj->setChildren([
            'test'   => [],
            'foobar' => []
        ]);

         $this->assertEquals(2, $obj->numChildren());
    }
}
