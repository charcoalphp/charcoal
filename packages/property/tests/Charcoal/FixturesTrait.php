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
        return $this->getPathToFixtures().'/'.ltrim($file, '/');
    }

    /**
     * Retrieve the path to the fixtures directory.
     *
     * @return string The path to the fixtures directory relative to the base directory.
     */
    public function getPathToFixtures()
    {
        return 'tests/Charcoal/Property/Fixture';
    }
}
