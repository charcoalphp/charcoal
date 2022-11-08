<?php

namespace Charcoal\Event;

use Stringable;

/**
 * Trait: InterruptableEventTrait
 * @package Charcoal\Event
 */
trait InterruptableEventTrait
{
    private bool $interrupted = false;

    /**
     * @var string|Stringable
     */
    private $reason;

    /**
     * @param string|Stringable $reason
     * @return void
     */
    public function interrupt($reason)
    {
        $this->reason = $reason;

        $this->interrupted = true;
    }

    public function isInterrupted(): bool
    {
        return $this->interrupted;
    }

    /**
     * @return string|Stringable
     */
    public function getReasonForInterruption()
    {
        return $this->reason;
    }
}
