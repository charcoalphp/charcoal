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
     * @return ArrayAccess The ArrayAccess implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
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
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testArrayAccess')]
    public function testOffsetGetReturnsNullOnNonexistentKey(ArrayAccess $obj): void
    {
        $this->assertNull($obj['xyz']);
    }

    /**
     * @covers  ::offsetExists()
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testArrayAccess')]
    public function testOffsetExistsReturnsFalseOnNonexistentKey(ArrayAccess $obj): void
    {
        $this->assertFalse(isset($obj['xyz']));
    }



    // Test Zero-Length Key
    // =========================================================================
    /**
     * @covers  ::offsetGet()
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testArrayAccess')]
    public function testOffsetGetReturnsNullOnZeroLengthKey(ArrayAccess $obj): void
    {
        $this->assertNull($obj['']);
    }

    /**
     * @covers  ::offsetExists()
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testArrayAccess')]
    public function testOffsetExistsReturnsFalseOnZeroLengthKey(ArrayAccess $obj): void
    {
        $this->assertFalse(isset($obj['']));
    }

    /**
     * @covers  ::offsetSet()
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testArrayAccess')]
    public function testOffsetSetIgnoredOnZeroLengthKey(ArrayAccess $obj): void
    {
        $obj[''] = 'waldo';
        $this->assertNull($obj['']);
    }

    /**
     * @covers  ::offsetUnset()
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testArrayAccess')]
    public function testOffsetUnsetIgnoredOnZeroLengthKey(ArrayAccess $obj): void
    {
        unset($obj['']);
        $this->assertNull($obj['']);
    }



    // Test Snake-Case Delimiter Key
    // =========================================================================
    /**
     * @covers  ::offsetGet()
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testArrayAccess')]
    public function testOffsetGetReturnsNullOnUnderscoreKey(ArrayAccess $obj): void
    {
        $this->assertNull($obj['_']);
    }

    /**
     * @covers  ::offsetExists()
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testArrayAccess')]
    public function testOffsetExistsReturnsFalseOnUnderscoreKey(ArrayAccess $obj): void
    {
        $this->assertFalse(isset($obj['_']));
    }

    /**
     * @covers  ::offsetSet()
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testArrayAccess')]
    public function testOffsetSetIgnoredOnUnderscoreKey(ArrayAccess $obj): void
    {
        $obj['_'] = 'waldo';
        $this->assertNull($obj['_']);
    }

    /**
     * @covers  ::offsetUnset()
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testArrayAccess')]
    public function testOffsetUnsetIgnoredOnUnderscoreKey(ArrayAccess $obj): void
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
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testArrayAccess')]
    public function testOffsetGetThrowsExceptionOnNumericKey(ArrayAccess $obj): void
    {
        $this->expectException(InvalidArgumentException::class);
        $obj[0];
    }

    /**
     * Asserts that a numeric key throws an exception, when assigning a value.
     *
     * @covers  ::offsetSet()
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testArrayAccess')]
    public function testOffsetSetThrowsExceptionOnNumericKey(ArrayAccess $obj): void
    {
        $this->expectException(InvalidArgumentException::class);
        $obj[0] = 'waldo';
    }

    /**
     * Asserts that a numeric key throws an exception, when looking up if a key/value exists.
     *
     * @covers  ::offsetExists()
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testArrayAccess')]
    public function testOffsetExistsThrowsExceptionOnNumericKey(ArrayAccess $obj): void
    {
        $this->expectException(InvalidArgumentException::class);
        $obj[0];
    }

    /**
     * Asserts that a numeric key throws an exception, when deleting a key/value.
     *
     * @covers  ::offsetUnset()
     *
     * @param  ArrayAccess $obj The ArrayAccess implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testArrayAccess')]
    public function testOffsetUnsetThrowsExceptionOnNumericKey(ArrayAccess $obj): void
    {
        $this->expectException(InvalidArgumentException::class);
        unset($obj[0]);
    }
}
