<?php

namespace Charcoal\Tests\Ui;

// From 'charcoal-ui'
use Charcoal\Ui\Menu\GenericMenu;
use Charcoal\Ui\ServiceProvider\MenuServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class GenericMenuTest extends AbstractTestCase
{
    use \Charcoal\Tests\Ui\ContainerIntegrationTrait;

    /**
     * @var GenericMenu $obj
     */
    public $obj;

    protected function setUp(): void
    {
        $container = $this->getContainer();
        $container->register(new MenuServiceProvider());

        $this->obj = new GenericMenu([
            'container'         => $container,
            'logger'            => $container['logger'],
            'view'              => $container['view'],
            'menu_item_builder' => $container['menu/item/builder'],
        ]);
    }

    public function testType(): void
    {
        $this->assertEquals('charcoal/ui/menu/generic', $this->obj->type());
    }
}
