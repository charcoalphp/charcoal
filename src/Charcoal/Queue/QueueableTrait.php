<?php

namespace Charcoal\Queue;

use \InvalidArgumentException;

/**
 * Full implementation, as a Trait, of the `QueueableInterface`.
 */
trait QueueableTrait
{
    /**
     * The queue ID.
     *
     * @var mixed $queueId
     */
    private $queueId;

    /**
     * Set the queue's ID.
     *
     * @param mixed $id The unique queue identifier.
     * @throws InvalidArgumentException If the ID isn't a string.
     * @return QueueableInterface Chainable
     */
    public function setQueueId($id)
    {
        if ($id === null) {
            $this->queueId = null;
            return $this;
        }

        if (!is_string($id)) {
            throw new InvalidArgumentException(
                'Queue ID must be a string'
            );
        }

        $this->queueId = $id;

        return $this;
    }

    /**
     * Get the queue's ID.
     *
     * @return string $queueId
     */
    public function queueId()
    {
        return $this->queueId;
    }

    /**
     * Set the date/time to process the queue.
     *
     * @param mixed $ts A date/time to initiate the queue processing.
     * @return mixed
     */
    abstract public function queue($ts = null);
}
