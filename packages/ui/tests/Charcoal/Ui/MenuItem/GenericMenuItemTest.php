<?php

namespace Charcoal\Tests\Ui;

// From 'charcoal-ui'
use Charcoal\Ui\MenuItem\GenericMenuItem;
use Charcoal\Ui\ServiceProvider\MenuServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class GenericMenuItemTest extends AbstractTestCase
{
    use \Charcoal\Tests\Ui\ContainerIntegrationTrait;

    /**
     * @var GenericMenuItem
     */
    public $obj;

    protected function setUp(): void
    {
        $container = $this->getContainer();
        $container->register(new MenuServiceProvider());

        $menu = $container['menu/builder']->build([]);

        $this->obj = new GenericMenuItem([
            'menu'              => $menu,
            'logger'            => $container['logger'],
            'view'              => $container['view'],
            'menu_item_builder' => $container['menu/item/builder'],
        ]);
    }

    public function testType(): void
    {
        $this->assertEquals('charcoal/ui/menu-item/generic', $this->obj->type());
    }
}
