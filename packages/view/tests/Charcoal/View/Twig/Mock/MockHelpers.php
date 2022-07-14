<?php

namespace Charcoal\Tests\View\Twig\Mock;

// From 'charcoal-view'
use Charcoal\View\Twig\HelpersInterface;

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
    public function toArray(): array
    {
        return [
            'foo' => [ 'A', 'B', 'C' ],
            'bar' => 'BAR',
        ];
    }
}
