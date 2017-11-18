<?php

namespace Charcoal\Tests\Config;

use PHPUnit_Framework_TestCase;

use InvalidArgumentException;
use StdClass;

use Charcoal\Config\SeparatorAwareTrait;

/**
 *
 */
class SeparatorAwareTraitTest extends PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = $this->getMockForTrait(SeparatorAwareTrait::class);
    }

    /**
     * Asserts that the separator is empty by default
     */
    public function testDefaultSeparatorIsEmpty()
    {
        $this->assertEquals('', $this->obj->separator());
    }

    /**
     * Asserts that calling `setSeparator()` with a non-string argument throws an InvalidArgumentException.
     *
     * @dataProvider notAStringProvider
     */
    public function testSetSeparatorNotAStringThrowsException($val)
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->obj->setSeparator($val);
    }

    /**
     * Asserts that calling the `setSeparator` with any string longer than 1 character throws an InvalidArgumentException.
     */
    public function testSetSeparatorLongStringThrowException()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->obj->setSeparator('..');
    }

    /**
     * Asserts that the `setSeparator()` method is chainable (returns self).
     */
    public function testSetSeparatorIsChainable()
    {
        $ret = $this->obj->setSeparator('.');
        $this->assertSame($ret, $this->obj);
    }

    /**
     * Asserts that calling `setSeparator()` actualy sets the separator (retrieved with `separator()`).
     */
    public function testSetSeparatorSetsSeparator()
    {
        $this->obj->setSeparator('/');
        $this->assertEquals('/', $this->obj->separator());
    }

    /**
     * Provider of invalid strings.
     *
     * @return array
     */
    public function notAStringProvider()
    {
        return [
          [new StdClass], [42], [true], [false], [[]], [[1, 2, 3]]
        ];
    }
}
