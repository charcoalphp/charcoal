<?php

namespace Charcoal\Event;

use League\Event\HasEventName;

/**
 * Generic Event
 */
final class GenericEvent extends Event implements HasEventName
{
    /**
     * @var mixed
     */
    private $subject;
    private array $arguments = [];
    private string $eventName;

    /**
     * @param string     $eventName
     * @param mixed|null $subject
     * @param array      $arguments
     */
    public function __construct(string $eventName, $subject = null, array $arguments = [])
    {
        $this->eventName = $eventName;
        $this->subject   = $subject;
        $this->arguments = $arguments;
    }

    /**
     * @return string
     */
    public function eventName(): string
    {
        return $this->getEventName();
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $subject Subject for GenericEvent.
     * @return self
     */
    public function setSubject($subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @param array $arguments Arguments for GenericEvent.
     * @return self
     */
    public function setArguments(array $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventName(): string
    {
        return $this->eventName;
    }

    /**
     * @param string $eventName EventName for GenericEvent.
     * @return self
     */
    public function setEventName(string $eventName): self
    {
        $this->eventName = $eventName;

        return $this;
    }
}
