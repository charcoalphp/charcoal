<?php

declare(strict_types=1);

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
     */
    public function toArray(): array
    {
        return [
            'foo' => [ 'A', 'B', 'C' ],
            'bar' => 'BAR',
        ];
    }
}
