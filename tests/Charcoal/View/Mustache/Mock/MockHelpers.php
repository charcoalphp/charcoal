<?php

namespace Charcoal\Tests\View\Mustache\Mock;

// From 'charcoal-view'
use Charcoal\View\Mustache\HelpersInterface;

/**
 *
 */
class MockHelpers implements HelpersInterface
{
    /**
     * Retrieve the helpers.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'foo' => [ 'A', 'B', 'C' ],
            'bar' => 'BAR',
        ];
    }
}
