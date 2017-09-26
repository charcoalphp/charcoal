<?php

namespace Charcoal\Tests\Ui;

use Charcoal\Ui\Layout\GenericLayout;
use Charcoal\Ui\ServiceProvider\LayoutServiceProvider;

/**
 *
 */
class GenericLayoutTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\Ui\ContainerIntegrationTrait;

    /**
     * @var GenericLayout $obj
     */
    public $obj;

    /**
     *
     */
    public function setUp()
    {
        $container = $this->getContainer();
        $container->register(new LayoutServiceProvider());

        $container['view'] = null;

        $this->obj = new GenericLayout();
    }

    public function testType()
    {
        $this->assertEquals('charcoal/ui/layout/generic', $this->obj->type());
    }
}
