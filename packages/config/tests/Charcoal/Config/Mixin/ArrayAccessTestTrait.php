<?php

namespace Charcoal\Tests\Config\Mixin;

use ArrayAccess;
use InvalidArgumentException;

/**
 * Test ArrayAccess implementations
 *
 * Only string keys are accepted.
 */
trait ArrayAccessTestTrait
{
    /**
     * Asserts that the object implements ArrayAccess.
     *
     * @coversNothing
     * @return ArrayAccess The ArrayAccess implementation to test.
     */
    abstract public function testArrayAccess();

    /**
     * @return void
     */
    abstract public function testOffsetGet();

    /**
     * @return void
     */
    abstract public function testOffsetExists();

    /**
     * @return void
     */
    abstract public function testOffsetSet();

    /**
     * @return void
     */
    abstract public function testOffsetUnset();



    // Test Nonexistent Key
    // =========================================================================

    /**
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
