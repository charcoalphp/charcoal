<?php

namespace Charcoal\Tests\Ui;

use Charcoal\Ui\MenuItem\GenericMenuItem;
use Charcoal\Ui\ServiceProvider\MenuServiceProvider;
use Charcoal\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GenericMenuItem::class)]
class GenericMenuItemTest extends AbstractTestCase
{
    use \Charcoal\Tests\Ui\ContainerIntegrationTrait;

    /**
     * @var GenericMenuItem
     */
    public $obj;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $container = $this->getContainer();
        (new MenuServiceProvider())->register($container);

        $menu = $container->get('menu/builder')->build([]);

        $this->obj = new GenericMenuItem([
            'menu'              => $menu,
            'logger'            => $container->get('logger'),
            'view'              => $container->get('view'),
            'menu_item_builder' => $container->get('menu/item/builder'),
        ]);
    }

    /**
     * @return void
     */
    public function testType()
    {
        $this->assertEquals('charcoal/ui/menu-item/generic', $this->obj->type());
    }
}
