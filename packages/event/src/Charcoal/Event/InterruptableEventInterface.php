<?php

namespace Charcoal\Event;

use Stringable;

/**
 * Interface: InterruptableEventInterface
 * @package Charcoal\Event
 */
interface InterruptableEventInterface
{
    public function isInterrupted(): bool;

    /**
     * @return string|Stringable
     */
    public function reason();
}
