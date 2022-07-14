<?php

namespace Charcoal\Queue;

use Charcoal\Model\ModelInterface;

/**
 *
 */
interface QueueItemInterface extends ModelInterface
{
    public const STATUS_SUCCESS = 'STATUS_SUCCESS';
    public const STATUS_FAILED = 'STATUS_FAILED';
    public const STATUS_RETRY = 'STATUS_RETRY';

    /**
     * Process the item.
     *
     * @param  callable $alwaysCallback  An optional callback routine executed after the item is processed.
     * @param  callable $successCallback An optional callback routine executed when the item is resolved.
     * @param  callable $failureCallback An optional callback routine executed when the item is rejected.
     * @return boolean|null Returns TRUE i this item was successfully processed,
     *     FALSE on failure or if an error occurs, NULL if this item is already processed.
     */
    public function process(
        callable $alwaysCallback = null,
        callable $successCallback = null,
        callable $failureCallback = null
    );

    /**
     * Set the queue item's data.
     *
     * @param array $data The queue item data to set.
     * @return self
     */
    public function setQueueItemData(array $data);

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
     * @return string
     */
    public function queueId();

    /**
     * Set the date/time the item was queued at.
     *
     * @param  null|string|\DateTimeInterface $ts A date/time string or object.
     * @return self
     */
    public function setQueuedDate($ts);

    /**
     * Retrieve the date/time the item was queued at.
     *
     * @return null|\DateTimeInterface
     */
    public function queuedDate();

    /**
     * Set the date/time the item should be processed at.
     *
     * @param  null|string|\DateTimeInterface $ts A date/time string or object.
     * @throws \InvalidArgumentException If the date/time is invalid.
     * @return self
     */
    public function setProcessingDate($ts);

    /**
     * Retrieve the date/time the item should be processed at.
     *
     * @return null|\DateTimeInterface
     */
    public function processingDate();

    /**
     * Set the date/time the item was processed at.
     *
     * @param  null|string|\DateTimeInterface $ts A date/time string or object.
     * @throws \InvalidArgumentException If the date/time is invalid.
     * @return self
     */
    public function setProcessedDate($ts);

    /**
     * Retrieve the date/time the item was processed at.
     *
     * @return null|\DateTimeInterface
     */
    public function processedDate();

    /**
     * Set the item's processed status.
     *
     * @param boolean $processed Whether the item has been processed.
     * @return self
     */
    public function setProcessed($processed);

    /**
     * Determine if the item has been processed.
     *
     * @return boolean
     */
    public function processed();

    /**
     * Retrieve the date/time the item should be expired at.
     *
     * @return null|\DateTimeInterface
     */
    public function expiryDate();

    /**
     * @param string $status Status for QueueItemTrait.
     * @return self
     */
    public function setStatus($status);

    /**
     * @return null|string
     */
    public function status();
}
