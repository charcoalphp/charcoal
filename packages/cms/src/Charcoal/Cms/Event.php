<?php

declare(strict_types=1);

namespace Charcoal\Cms;

/**
 * CMS Event
 */
final class Event extends AbstractEvent
{
    /**
     *
     * @see CategorizableTrait::$ategoryType()
     */
    public function categoryType(): string
    {
        return EventCategory::class;
    }
}
