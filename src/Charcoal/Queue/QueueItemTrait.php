<?php

namespace Charcoal\Queue;

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
    * @var mixed $queue_id
    */
    private $queue_id;
    /**
    * @var boolean $processed
    */
    private $processed = false;
    /**
    * @var DateTime $queued_date
    */
    private $queued_date;

    /**
    * The date/time at which this queue item job should be ran.
    * If null, 0, or past, then it should be performed immediately.
    * @var DateTime $processing_date;
    */
    private $processing_date;

    /**
    * @var DateTime $queued_date
    */
    private $processed_date;

    /**
    * @return boolean Success / Failure
    */
    abstract public function process(
        callable $callback = null,
        callable $success_callback = null,
        callable $failure_callback = null
    );

    /**
    * @param array $data
    */
    public function set_queue_item_data(array $data)
    {
        if (isset($data['queue_id']) && $data['queue_id']) {
            $this->set_queue_id($data['queue_id']);
        }
        if (isset($data['processed']) && $data['processed']) {
            $this->set_processed($data['processed']);
        }
        if (isset($data['queued_date']) && $data['queued_date']) {
            $this->set_queue_id($data['queue_id']);
        }
        if (isset($data['processed_date']) && $data['processed_date']) {
            $this->set_processed_date($data['processed_date']);
        }
    }

    /**
    * @param mixed $queue_id
    * @return QueueItemInterface Chainable
    */
    public function set_queue_id($queue_id)
    {
        $this->queue_id = $queue_id;
        return $this;
    }

    /**
    * @return mixed
    */
    public function queue_id()
    {
        return $this->queue_id;
    }

    public function set_processed($processed)
    {
        $this->processed = !!$processed;
        return $this;
    }

    public function processed()
    {
        return $this->processed;
    }

    /**
    * @param string|DateTime $ts
    * @throws InvalidArgumentException
    * @return QueueItemInterface Chainable
    */
    public function set_queued_date($ts)
    {
        if ($ts === null) {
            $this->queued_date = null;
            return $this;
        }
        if (is_string($ts)) {
            try {
                $ts = new DateTime($ts);
            } catch (Exception $e) {
                throw new InvalidArgumentException(
                    'Can not set queued date: '.$e->getMessage()
                );
            }
        }
        if (!($ts instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                'Invalid "Queued Date" value. Must be a date/time string or a DateTime object.'
            );
        }
        $this->queued_date = $ts;
        return $this;
    }

    /**
    * @return DateTime|null
    */
    public function queued_date()
    {
        return $this->queued_date;
    }

    /**
    * @param null|string|DateTime $ts
    * @throws InvalidArgumentException
    * @return QueueItemInterface Chainable
    */
    public function set_processing_date($ts)
    {
        if ($ts === null) {
            $this->processing_date = null;
            return $this;
        }
        if (is_string($ts)) {
            try {
                $ts = new DateTime($ts);
            } catch (Exception $e) {
                throw new InvalidArgumentException($e->getMessage().' ('.$ts.')');
            }
        }
        if (!($ts instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                'Invalid "Processing Date" value. Must be a date/time string or a DateTime object.'
            );
        }
        $this->processing_date = $ts;
        return $this;
    }

    /**
    * @return DateTime|null
    */
    public function processing_date()
    {
        return $this->processing_date;
    }

    /**
    * @param null|string|DateTime $ts
    * @throws InvalidArgumentException
    * @return QueueItemInterface Chainable
    */
    public function set_processed_date($ts)
    {
        if ($ts === null) {
            $this->processed_date = null;
            return $this;
        }
        if (is_string($ts)) {
            try {
                $ts = new DateTime($ts);
            } catch (Exception $e) {
                throw new InvalidArgumentException($e->getMessage().' ('.$ts.')');
            }
        }
        if (!($ts instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                'Invalid "Processed Date" value. Must be a date/time string or a DateTime object.'
            );
        }
        $this->processed_date = $ts;
        return $this;
    }

    /**
    * @return DateTime|null
    */
    public function processed_date()
    {
        return $this->processed_date;
    }

    public function pre_save_queue_item()
    {
        $this->set_processed(false);
        $this->set_queued_date('now');
    }
}
