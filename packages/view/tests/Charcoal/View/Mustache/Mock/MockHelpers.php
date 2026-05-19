<?php

declare(strict_types=1);

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
     */
    public function toArray(): array
    {
        return [
            'foo' => [ 'A', 'B', 'C' ],
            'bar' => 'BAR',
        ];
    }
}
