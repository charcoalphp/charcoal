<?php

namespace Charcoal\Email\Queue;

/**
 *
 */
interface QueueItemInterface
{
    /**
     * Process the item.
     *
     * @param  callable $callback        An optional callback routine executed after the item is processed.
     * @param  callable $successCallback An optional callback routine executed when the item is resolved.
     * @param  callable $failureCallback An optional callback routine executed when the item is rejected.
     * @return boolean  Success / Failure
     */
    public function process(
        callable $callback = null,
        callable $successCallback = null,
        callable $failureCallback = null
    );

    /**
     * Set the item's processed status.
     *
     * @param boolean $processed Whether the item has been processed.
     * @return QueueItemInterface Chainable
     */
    public function setProcessed($processed);

    /**
     * Determine if the item has been processed.
     *
     * @return boolean
     */
    public function processed();

    /**
     * Set the queue item's data.
     *
     * @param array $data The queue item data to set.
     * @return QueueItemTrait Chainable
     */
    public function setQueueItemData(array $data);

    /**
     * Set the queue's ID.
     *
     * @param mixed $id The unique queue identifier.
     * @return QueueItemInterface Chainable
     */
    public function setQueueId($id);

    /**
     * Get the queue's ID.
     *
     * @return string
     */
    public function queueId();

    /**
     * Set the date/time the item was queued at.
     *
     * @param  null|string|DateTimeInterface $ts A date/time string or object.
     * @return QueueItemInterface Chainable
     */
    public function setQueuedDate($ts);

    /**
     * Retrieve the date/time the item was queued at.
     *
     * @return null|DateTimeInterface
     */
    public function queuedDate();

    /**
     * Set the date/time the item should be processed at.
     *
     * @param  null|string|DateTimeInterface $ts A date/time string or object.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return QueueItemInterface Chainable
     */
    public function setProcessingDate($ts);

    /**
     * Retrieve the date/time the item should be processed at.
     *
     * @return null|DateTimeInterface
     */
    public function processingDate();

    /**
     * Set the date/time the item was processed at.
     *
     * @param  null|string|DateTimeInterface $ts A date/time string or object.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return QueueItemInterface Chainable
     */
    public function setProcessedDate($ts);

    /**
     * Retrieve the date/time the item was processed at.
     *
     * @return null|DateTimeInterface
     */
    public function processedDate();
}
