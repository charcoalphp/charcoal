<?php

declare(strict_types=1);

namespace Charcoal\Tests\Cms\Mock;

// From 'charcoal-app'
use Charcoal\App\Template\AbstractTemplate;

// From 'charcoal-cms'
use Charcoal\Cms\EventInterface;

/**
 * Event Template Controller
 */
class EventTemplate extends AbstractTemplate
{
    private ?\Charcoal\Cms\EventInterface $event = null;

    /**
     * @return EventInterface
     */
    public function event(): ?\Charcoal\Cms\EventInterface
    {
        return $this->event;
    }

    /**
     * @param  EventInterface $event The current event.
     */
    public function setEvent(EventInterface $event): static
    {
        $this->event = $event;

        return $this;
    }
}
