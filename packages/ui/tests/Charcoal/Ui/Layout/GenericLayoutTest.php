<?php

namespace Charcoal\Tests\Ui;

// From 'charcoal-ui'
use Charcoal\Ui\Layout\GenericLayout;
use Charcoal\Ui\ServiceProvider\LayoutServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class GenericLayoutTest extends AbstractTestCase
{
    use \Charcoal\Tests\Ui\ContainerIntegrationTrait;

    /**
     * @var GenericLayout
     */
    public $obj;

    protected function setUp(): void
    {
        $container = $this->getContainer();
        $container->register(new LayoutServiceProvider());

        $container['view'] = null;

        $this->obj = new GenericLayout();
    }

    public function testType(): void
    {
        $this->assertEquals('charcoal/ui/layout/generic', $this->obj->type());
    }
}
