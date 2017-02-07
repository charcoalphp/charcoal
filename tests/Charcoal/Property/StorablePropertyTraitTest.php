<?php

namespace Charcoal\Tests\Property;

use PHPUnit_Framework_TestCase;

use PDO;

use Charcoal\Translation\TranslationString;

use Charcoal\Property\StorablePropertyTrait;

/**
 *
 */
class StorablePropertyTraitTest extends PHPUnit_Framework_TestCase
{
    private $obj;

    public function setUp()
    {
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
            ->will($this->returnValue($GLOBALS['translator']));
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
