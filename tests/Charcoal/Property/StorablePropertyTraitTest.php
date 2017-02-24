<?php

namespace Charcoal\Tests\Property;

use Charcoal\Property\StorablePropertyTrait;

/**
 *
 */
class StorablePropertyTraitTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    private $obj;

    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = $this->getMockForTrait(StorablePropertyTrait::class);

        $this->obj
            ->expects($this->any())
            ->method('l10n')
            ->will($this->returnValue(false));

        $this->obj
            ->expects($this->any())
            ->method('multiple')
            ->will($this->returnValue(false));

        $this->obj
            ->expects($this->any())
            ->method('translator')
            ->will($this->returnValue($container['translator']));
    }

    public function testFields()
    {
        $ret = $this->obj->fields('foo');
        $this->assertEquals(1, count($ret));
        $this->assertEquals(null, $ret[0]->ident());

        $ret2 = $this->obj->fields('foo');
        $this->assertEquals(1, count($ret));
        $this->assertEquals(null, $ret[0]->ident());
    }
}
