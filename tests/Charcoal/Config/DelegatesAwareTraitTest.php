<?php

namespace Charcoal\Tests\Config;

use PHPUnit_Framework_TestCase;

use InvalidArgumentException;
use StdClass;

use Charcoal\Config\DelegatesAwareTrait;
use Charcoal\Config\AbstractEntity;

/**
 *
 */
class DelegatesAwareTraitTest extends PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = $this->getMockForTrait(DelegatesAwareTrait::class);
    }

    /**
     * Asserts that the `setDelegates` method is chainable.
     */
    public function testSetDelegatesIsChainable()
    {
        $delegate = $this->getMockForAbstractClass(AbstractEntity::class);
        $ret = $this->obj->setDelegates([$delegate]);
        $this->assertSame($ret, $this->obj);
    }

    /**
     * Asserts that the `addDelegate` method is chainable.
     */
    public function testAddDelegateIsChainable()
    {
        $delegate = $this->getMockForAbstractClass(AbstractEntity::class);
        $ret = $this->obj->addDelegate($delegate);
        $this->assertSame($ret, $this->obj);
    }

    /**
     * Asserts that the `prependDelegate` method is chainable.
     */
    public function testPrependDelegateIsChainable()
    {
        $delegate = $this->getMockForAbstractClass(AbstractEntity::class);
        $ret = $this->obj->prependDelegate($delegate);
        $this->assertSame($ret, $this->obj);
    }

}
