<?php

namespace Charcoal\Queue;

/**
 * Queuable objects can be queued.
 */
interface QueueableInterface
{
    /**
     * Set the queue's ID.
     *
     * @param mixed $id The unique queue identifier.
     * @return QueueableInterface Chainable
     */
    public function setQueueId($id);

    /**
     * Get the queue's ID.
     *
     * @return string $queueId
     */
    public function queueId();

    /**
     * Set the date/time to process the queue.
     *
     * @param mixed $ts A date/time to initiate the queue processing.
     * @return QueueableInterface Chainable
     */
    public function queue($ts = null);
}
