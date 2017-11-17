<?php

namespace Charcoal\Tests;

/**
 * Utilities for advanced assertions.
 */
trait AssertionsTrait
{
    /**
     * Assert that the given haystack is as expected.
     *
     * @param  array $expected The expected haystack.
     * @param  array $haystack The actual haystack.
     * @return void
     */
    public function assertArrayEquals(array $expected, array $haystack)
    {
        $this->assertCount(count($expected), $haystack);
        $this->assertEquals($expected, $haystack);
    }

    /**
     * Assert that the given haystack contains the expected.
     *
     * @param  array $expected The expected haystack.
     * @param  array $haystack The actual haystack.
     * @return void
     */
    public function assertArrayContains(array $expected, array $haystack)
    {
        foreach ($expected as $item) {
            $this->assertContains($item, $haystack);
        }
    }
}
