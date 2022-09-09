<?php

namespace Charcoal\Property\Event;

use Charcoal\Property\PropertyInterface;
use League\Event\HasEventName;

/**
 * Property Event
 *
 * Event that
 */
class PropertyEvent implements HasEventName
{
    private const EVENT_PREFIX = 'property';
    public const  EVENT_SAVE   = 'save';
    public const  EVENT_UPDATE = 'update';

    private string            $event;
    private PropertyInterface $property;

    /**
     * @param string            $event    The event name.
     * @param PropertyInterface $property The property triggering the event.
     */
    public function __construct(string $event, PropertyInterface $property)
    {
        $this->event    = $event;
        $this->property = $property;
    }

    /**
     * @return string
     */
    public function eventName(): string
    {
        return $this->generateEventName($this->getEvent(), $this->getProperty()->type());
    }

    /**
     * @param string $event        The event name.
     * @param string $propertyType The property type.
     * @return string
     */
    public static function generateEventName(string $event, string $propertyType): string
    {
        return implode('.', [self::EVENT_PREFIX, $propertyType, $event]);
    }

    /**
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * The property triggering the event.
     *
     * @return PropertyInterface
     */
    public function getProperty(): PropertyInterface
    {
        return $this->property;
    }
}
