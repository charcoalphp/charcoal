<?php

namespace Charcoal\Tests;

/**
 * Utilities for interaction with fixtures.
 */
trait FixturesTrait
{
    /**
     * Retrieve the file path to the given fixture.
     *
     * @param  string $file The file path relative to the Fixture directory.
     * @return string The file path to the fixture relative to the base directory.
     */
    public function getPathToFixture($file)
    {
        return __DIR__.'/../../tests/Charcoal/Config/Fixture/'.ltrim($file, '/');
    }
}
