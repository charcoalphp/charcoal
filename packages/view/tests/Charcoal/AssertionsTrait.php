<?php

namespace Charcoal\Tests;

use ArrayAccess;
use Countable;
use Traversable;
use PHPUnit\Exception;
use PHPUnit\Framework\Constraint\ArraySubset;
use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Util\InvalidArgumentHelper;

/**
 * Utilities for advanced assertions.
 */
trait AssertionsTrait
{
    /**
     * Asserts that the given haystack is as expected.
     *
     * @param  array|Countable|Traversable $expected The expected haystack.
     * @param  array|Countable|Traversable $actual   The actual haystack.
     * @param  string                      $message  The error to report.
     */
    public function assertArrayEquals(array $expected, array $actual, $message = ''): void
    {
        $this->assertSameSize($expected, $actual, $message);
        $this->assertEquals($expected, $actual, $message);
    }

    /**
     * Asserts that the given haystack contains the expected values.
     *
     * @param  array|Traversable $needles The expected values.
     * @param  mixed             $array   The array to search.
     * @param  string            $message The error to report.
     * @throws Exception If argument is invalid.
     */
    public function assertArrayContains($needles, $array, $message = ''): void
    {
        if (!is_array($needles) &&
            !($needles instanceof Traversable)) {
            $invalidArgHelper = $this->getInvalidArgumentHelperClass();
            throw $invalidArgHelper::factory(
                1,
                'array or Traversable'
            );
        }

        foreach ($needles as $needle) {
            $this->assertContains($needle, $array, $message);
        }
    }

    /**
     * Asserts that the given haystack contains the expected keys.
     *
     * @param  array|Traversable $keys    The expected keys.
     * @param  mixed             $array   The array to search.
     * @param  string            $message The error to report.
     * @throws Exception If argument is invalid.
     */
    public function assertArrayHasKeys($keys, $array, $message = ''): void
    {
        if (!is_array($keys) &&
            !($keys instanceof Traversable)) {
            $invalidArgHelper = $this->getInvalidArgumentHelperClass();
            throw $invalidArgHelper::factory(
                1,
                'array or Traversable'
            );
        }

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $array, $message);
        }
    }

    /**
     * Asserts that the given haystack contains the expected subsets.
     *
     * @param  array|Traversable $subsets The expected subsets.
     * @param  mixed             $array   The array to search.
     * @param  boolean           $strict  Whether to check for object identity.
     * @param  string            $message The error to report.
     * @throws Exception If argument is invalid.
     */
    public function assertArraySubsets(
        $subsets,
        $array,
        $strict = false,
        $message = ''
    ): void {
        if (!is_array($subsets) &&
            !($subsets instanceof Traversable)) {
            $invalidArgHelper = $this->getInvalidArgumentHelperClass();
            throw $invalidArgHelper::factory(
                1,
                'array or Traversable'
            );
        }

        foreach ($subsets as $key => $val) {
            $this->assertArraySubset([ $key => $val ], $array, $strict, $message);
        }
    }

    /**
     * Asserts that an array does not have a specified subset.
     *
     * @param  array|ArrayAccess $subset  The expected subset.
     * @param  array|ArrayAccess $array   The array to search.
     * @param  boolean           $strict  Whether to check for object identity.
     * @param  string            $message The error to report.
     * @throws Exception If argument is invalid.
     */
    public function assertNotArraySubset(
        $subset,
        $array,
        $strict = false,
        $message = ''
    ): void {
        if (!is_array($subset) && !$subset instanceof ArrayAccess) {
            $invalidArgHelper = $this->getInvalidArgumentHelperClass();
            throw $invalidArgHelper::factory(
                1,
                'array or ArrayAccess'
            );
        }

        // phpcs:disable Squiz.Objects.ObjectInstantiation.NotAssigned
        $logicalNot  = $this->getLogicalNotClass();
        $arraySubset = $this->getArraySubsetClass();
        $constraint  = new $logicalNot(
            new $arraySubset($subset, $strict)
        );
        // phpcs:enable

        static::assertThat($array, $constraint, $message);
    }

    /**
     * Retrieve the correct version of the `InvalidArgumentHelper` class.
     */
    protected function getInvalidArgumentHelperClass(): string
    {
        $class57 = 'PHPUnit_Util_InvalidArgumentHelper';
        return class_exists($class57) ? $class57 : InvalidArgumentHelper::class;
    }

    /**
     * Retrieve the correct version of the `LogicalNot` class.
     */
    protected function getLogicalNotClass(): string
    {
        $class57 = 'PHPUnit_Framework_Constraint_Not';
        return class_exists($class57) ? $class57 : LogicalNot::class;
    }

    /**
     * Retrieve the correct version of the `ArraySubset` class.
     */
    protected function getArraySubsetClass(): string
    {
        $class57 = 'PHPUnit_Framework_Constraint_ArraySubset';
        return class_exists($class57) ? $class57 : ArraySubset::class;
    }
}
