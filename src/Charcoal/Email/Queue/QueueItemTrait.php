<?php

namespace Charcoal\Email\Queue;

// Dependencies from `PHP`
use \DateTime;
use \DateTimeInterface;
use \Exception;
use \InvalidArgumentException;

/**
 *
 */
trait QueueItemTrait
{
    /**
     * The queue ID.
     *
     * @var mixed $queueId
     */
    private $queueId;

    /**
     * Whether the item has been processed.
     *
     * @var boolean $processed
     */
    private $processed = false;

    /**
     * When the item was queued.
     *
     * @var DateTimeInterface $queuedDate
     */
    private $queuedDate;

    /**
     * When the item should be processed.
     *
     * The date/time at which this queue item job should be ran.
     * If NULL, 0, or a past date/time, then it should be performed immediately.
     *
     * @var DateTimeInterface $processingDate
     */
    private $processingDate;

    /**
     * When the item was processed.
     *
     * @var DateTimeInterface $processedDate
     */
    private $processedDate;

    /**
     * Process the item.
     *
     * @param  callable $callback        An optional callback routine executed after the item is processed.
     * @param  callable $successCallback An optional callback routine executed when the item is resolved.
     * @param  callable $failureCallback An optional callback routine executed when the item is rejected.
     * @return boolean  Success / Failure
     */
    abstract public function process(
        callable $callback = null,
        callable $successCallback = null,
        callable $failureCallback = null
    );

    /**
     * Set the queue's ID.
     *
     * @param mixed $id The unique queue identifier.
     * @return QueueItemInterface Chainable
     */
    public function setQueueId($id)
    {
        $this->queueId = $id;

        return $this;
    }

    /**
     * Get the queue's ID.
     *
     * @return mixed
     */
    public function queueId()
    {
        return $this->queueId;
    }

    /**
     * Set the item's processed status.
     *
     * @param boolean $processed Whether the item has been processed.
     * @return QueueItemInterface Chainable
     */
    public function setProcessed($processed)
    {
        $this->processed = !!$processed;
        return $this;
    }

    /**
     * Determine if the item has been processed.
     *
     * @return boolean
     */
    public function processed()
    {
        return $this->processed;
    }

    /**
     * Set the date/time the item was queued at.
     *
     * @param  null|string|DateTimeInterface $ts A date/time string or object.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return QueueItemInterface Chainable
     */
    public function setQueuedDate($ts)
    {
        if ($ts === null) {
            $this->queuedDate = null;
            return $this;
        }

        if (is_string($ts)) {
            try {
                $ts = new DateTime($ts);
            } catch (Exception $e) {
                throw new InvalidArgumentException(
                    sprintf('Can not set queued date: %s', $e->getMessage())
                );
            }
        }

        if (!($ts instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                'Invalid "Queued Date" value. Must be a date/time string or a DateTime object.'
            );
        }

        $this->queuedDate = $ts;

        return $this;
    }

    /**
     * Retrieve the date/time the item was queued at.
     *
     * @return null|DateTimeInterface
     */
    public function queuedDate()
    {
        return $this->queuedDate;
    }

    /**
     * Set the date/time the item should be processed at.
     *
     * @param  null|string|DateTimeInterface $ts A date/time string or object.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return QueueItemInterface Chainable
     */
    public function setProcessingDate($ts)
    {
        if ($ts === null) {
            $this->processingDate = null;
            return $this;
        }

        if (is_string($ts)) {
            try {
                $ts = new DateTime($ts);
            } catch (Exception $e) {
                throw new InvalidArgumentException(
                    sprintf('%s (%s)', $e->getMessage(), $ts)
                );
            }
        }

        if (!($ts instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                'Invalid "Processing Date" value. Must be a date/time string or a DateTime object.'
            );
        }

        $this->processingDate = $ts;

        return $this;
    }

    /**
     * Retrieve the date/time the item should be processed at.
     *
     * @return null|DateTimeInterface
     */
    public function processingDate()
    {
        return $this->processingDate;
    }

    /**
     * Set the date/time the item was processed at.
     *
     * @param  null|string|DateTimeInterface $ts A date/time string or object.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return QueueItemInterface Chainable
     */
    public function setProcessedDate($ts)
    {
        if ($ts === null) {
            $this->processedDate = null;
            return $this;
        }

        if (is_string($ts)) {
            try {
                $ts = new DateTime($ts);
            } catch (Exception $e) {
                throw new InvalidArgumentException(
                    sprintf('%s (%s)', $e->getMessage(), $ts)
                );
            }
        }

        if (!($ts instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                'Invalid "Processed Date" value. Must be a date/time string or a DateTime object.'
            );
        }

        $this->processedDate = $ts;

        return $this;
    }

    /**
     * Retrieve the date/time the item was processed at.
     *
     * @return null|DateTimeInterface
     */
    public function processedDate()
    {
        return $this->processedDate;
    }

    /**
     * Hook called before saving the item.
     *
     * Presets the item as _to-be_ processed and queued now.
     *
     * @return QueueItemInterface Chainable
     */
    public function preSaveQueueItem()
    {
        $this->setProcessed(false);
        $this->setQueuedDate('now');

        return $this;
    }
}
