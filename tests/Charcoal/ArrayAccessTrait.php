<?php

namespace Charcoal\Tests;

use ArrayAccess;
use InvalidArgumentException;

/**
 * Test ArrayAccess implementations
 *
 * Only string keys are accepted.
 */
trait ArrayAccessTrait
{
    /**
     * Asserts that the object implements ArrayAccess.
     *
     * @coversNothing
     * @return ArrayAccess The ArrayAccess implementation to test.
     */
    abstract public function testArrayAccess();

    /**
     * @covers ::offsetGet()
     * @return void
     */
    abstract public function testOffsetGet();

    /**
     * @covers ::offsetExists()
     * @return void
     */
    abstract public function testOffsetExists();

    /**
     * @covers ::offsetSet()
     * @return void
     */
    abstract public function testOffsetSet();

    /**
     * @covers ::offsetUnset()
     * @return void
     */
    abstract public function testOffsetUnset();



    // Test Nonexistent Key
    // =========================================================================

    /**
     * @covers  ::offsetGet()
     * @depends testArrayAccess
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     * @return void
     */
    public function testOffsetGetReturnsNullOnNonexistentKey(ArrayAccess $obj)
    {
        $this->assertNull($obj['xyz']);
    }

    /**
     * @covers  ::offsetExists()
     * @depends testArrayAccess
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     * @return void
     */
    public function testOffsetExistsReturnsFalseOnNonexistentKey(ArrayAccess $obj)
    {
        $this->assertFalse(isset($obj['xyz']));
    }



    // Test Zero-Length Key
    // =========================================================================

    /**
     * @covers  ::offsetGet()
     * @depends testArrayAccess
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     * @return void
     */
    public function testOffsetGetReturnsNullOnZeroLengthKey(ArrayAccess $obj)
    {
        $this->assertNull($obj['']);
    }

    /**
     * @covers  ::offsetExists()
     * @depends testArrayAccess
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     * @return void
     */
    public function testOffsetExistsReturnsFalseOnZeroLengthKey(ArrayAccess $obj)
    {
        $this->assertFalse(isset($obj['']));
    }

    /**
     * @covers  ::offsetSet()
     * @depends testArrayAccess
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     * @return void
     */
    public function testOffsetSetIgnoredOnZeroLengthKey(ArrayAccess $obj)
    {
        $obj[''] = 'waldo';
        $this->assertNull($obj['']);
    }

    /**
     * @covers  ::offsetUnset()
     * @depends testArrayAccess
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     * @return void
     */
    public function testOffsetUnsetIgnoredOnZeroLengthKey(ArrayAccess $obj)
    {
        unset($obj['']);
        $this->assertNull($obj['']);
    }



    // Test Snake-Case Delimiter Key
    // =========================================================================

    /**
     * @covers  ::offsetGet()
     * @depends testArrayAccess
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     * @return void
     */
    public function testOffsetGetReturnsNullOnUnderscoreKey(ArrayAccess $obj)
    {
        $this->assertNull($obj['_']);
    }

    /**
     * @covers  ::offsetExists()
     * @depends testArrayAccess
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     * @return void
     */
    public function testOffsetExistsReturnsFalseOnUnderscoreKey(ArrayAccess $obj)
    {
        $this->assertFalse(isset($obj['_']));
    }

    /**
     * @covers  ::offsetSet()
     * @depends testArrayAccess
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     * @return void
     */
    public function testOffsetSetIgnoredOnUnderscoreKey(ArrayAccess $obj)
    {
        $obj['_'] = 'waldo';
        $this->assertNull($obj['_']);
    }

    /**
     * @covers  ::offsetUnset()
     * @depends testArrayAccess
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     * @return void
     */
    public function testOffsetUnsetIgnoredOnUnderscoreKey(ArrayAccess $obj)
    {
        unset($obj['']);
        $this->assertNull($obj['_']);
    }


    // Test Numeric Key
    // =========================================================================

    /**
     * Asserts that a numeric key throws an exception, when retrieving a value.
     *
     * @covers  ::offsetGet()
     * @depends testArrayAccess
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     * @return void
     */
    public function testOffsetGetThrowsExceptionOnNumericKey(ArrayAccess $obj)
    {
        $this->expectException(InvalidArgumentException::class);
        $obj[0];
    }

    /**
     * Asserts that a numeric key throws an exception, when assigning a value.
     *
     * @covers  ::offsetSet()
     * @depends testArrayAccess
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     * @return void
     */
    public function testOffsetSetThrowsExceptionOnNumericKey(ArrayAccess $obj)
    {
        $this->expectException(InvalidArgumentException::class);
        $obj[0] = 'waldo';
    }

    /**
     * Asserts that a numeric key throws an exception, when looking up if a key/value exists.
     *
     * @covers  ::offsetExists()
     * @depends testArrayAccess
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     * @return void
     */
    public function testOffsetExistsThrowsExceptionOnNumericKey(ArrayAccess $obj)
    {
        $this->expectException(InvalidArgumentException::class);
        isset($obj[0]);
    }

    /**
     * Asserts that a numeric key throws an exception, when deleting a key/value.
     *
     * @covers  ::offsetUnset()
     * @depends testArrayAccess
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     * @return void
     */
    public function testOffsetUnsetThrowsExceptionOnNumericKey(ArrayAccess $obj)
    {
        $this->expectException(InvalidArgumentException::class);
        unset($obj[0]);
    }
}
