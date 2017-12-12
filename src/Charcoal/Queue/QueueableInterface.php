<?php

namespace Charcoal\Queue;

/**
 * Queueable objects can be added queue.
 */
interface QueueableInterface
{
    /**
     * Set the queue's ID.
     *
     * @param mixed $id The unique queue identifier.
     * @return self
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
     * @return self
     */
    public function queue($ts = null);
}
