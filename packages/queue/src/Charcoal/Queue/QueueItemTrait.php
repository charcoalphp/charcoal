<?php

namespace Charcoal\Queue;

use DateInterval;
use DateTime;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;

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
     * @var DateTimeInterface|null $queuedDate
     */
    private $queuedDate;

    /**
     * When the item should be processed.
     *
     * The date/time at which this queue item job should be ran.
     * If NULL, 0, or a past date/time, then it should be performed immediately.
     *
     * @var DateTimeInterface|null $processingDate
     */
    private $processingDate;

    /**
     * When the item was processed.
     *
     * @var DateTimeInterface|null $processedDate
     */
    private $processedDate;

    /**
     * When the item should be considered expired.
     *
     * The date/time at which this queue item job should expire and be prevented to fire.
     * If NULL, 0, or a future date/time, then it should be allowed to be performed.
     *
     * @var DateTimeInterface|null $lexpiryDate
     */
    private $expiryDate;

    /**
     * Default amount of seconds before expiry after processing date.
     *
     * @var integer $defaultExpiryInSeconde
     */
    private $defaultExpiryInSeconds = 86400;

    /**
     * The status of the queue item.
     *
     * @var string
     */
    private $status;

    /**
     * Process the item.
     *
     * @param  callable $alwaysCallback  An optional callback routine executed after the item is processed.
     * @param  callable $successCallback An optional callback routine executed when the item is resolved.
     * @param  callable $failureCallback An optional callback routine executed when the item is rejected.
     * @return boolean|null Returns TRUE i this item was successfully processed,
     *     FALSE on failure or if an error occurs, NULL if this item is already processed.
     */
    abstract public function process(
        callable $alwaysCallback = null,
        callable $successCallback = null,
        callable $failureCallback = null
    );

    /**
     * @param Exception $e Exception to log.
     * @return void
     */
    protected function logProcessingException(Exception $e)
    {
        $this->logger->error(
            sprintf('Could not process a queue item: %s', $e->getMessage()),
            [
                'manager' => get_called_class(),
                'queueId' => $this->queueId(),
                'itemId'  => $this->id(),
            ]
        );
    }

    /**
     * Set the queue item's data.
     *
     * @param array $data The queue item data to set.
     * @return self
     */
    public function setQueueItemData(array $data)
    {
        if (isset($data['queue_id'])) {
            $this->setQueueId($data['queue_id']);
        }

        if (isset($data['processed'])) {
            $this->setProcessed($data['processed']);
        }

        if (isset($data['queued_date'])) {
            $this->setQueuedDate($data['queue_date']);
        }

        if (isset($data['processed_date'])) {
            $this->setProcessedDate($data['processed_date']);
        }

        return $this;
    }

    /**
     * Set the queue's ID.
     *
     * @param mixed $id The unique queue identifier.
     * @return self
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
     * @return self
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
     * @return self
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
     * @return null|\DateTimeInterface
     */
    public function queuedDate()
    {
        return $this->queuedDate;
    }

    /**
     * Set the date/time the item should be processed at.
     *
     * @param  null|string|\DateTimeInterface $ts A date/time string or object.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return self
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
     * @return null|\DateTimeInterface
     */
    public function processingDate()
    {
        return $this->processingDate;
    }

    /**
     * Set the date/time the item was processed at.
     *
     * @param  null|string|\DateTimeInterface $ts A date/time string or object.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return self
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
     * @return null|\DateTimeInterface
     */
    public function processedDate()
    {
        return $this->processedDate;
    }

    /**
     * Retrieve the date/time the item should be expired at.
     *
     * @return null|\DateTimeInterface
     */
    public function expiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * Set the date/time the item will expire at.
     *
     * @param  null|string|\DateTimeInterface $ts A date/time string or object.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return self
     */
    public function setExpiryDate($ts)
    {
        if ($ts === null) {
            $this->expiryDate = null;
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
                'Invalid "Expiry Date" value. Must be a date/time string or a DateTime object.'
            );
        }

        $this->expiryDate = $ts;

        return $this;
    }

    /**
     * @return string
     */
    public function status()
    {
        return $this->status;
    }

    /**
     * @param string $status Status for QueueItemTrait.
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Hook called before saving the item.
     *
     * Presets the item as _to-be_ processed and queued now.
     *
     * @return self
     */
    protected function preSaveQueueItem()
    {
        $this->setProcessed(false);
        $this->setQueuedDate('now');

        if (!$this->expiryDate()) {
            $this->generateExpiry();
        }

        return $this;
    }

    /**
     * Generate an expiry date based on the default interval and the scheduled processing date.
     *
     * @return self
     */
    protected function generateExpiry()
    {
        $date = (clone $this['processingDate'] ?? new DateTime());
        $date->add(new DateInterval('PT' . $this->defaultExpiryInSeconds . 'S'));

        return $this->setExpiryDate($date);
    }
}
