<?php

namespace Charcoal\Queue;

/**
*
*/
interface QueueItemInterface
{
    /**
    * @return boolean Success / Failure
    */
    public function process(
        callable $callback = null,
        callable $success_callback = null,
        callable $failure_callback = null
    );

    /**
    * @param array $data
    */
    public function set_queue_item_data(array $data);

    /**
    * @param string $queue_id
    * @return QueueItemInterface Chainable
    */
    public function set_queue_id($queue_id);

    /**
    * @return string
    */
    public function queue_id();

    /**
    * @param string|DateTime $ts
    * @throws InvalidArgumentException
    * @return QueueItemInterface Chainable
    */
    public function set_queued_date($ts);

    /**
    * @return DateTime|null
    */
    public function queued_date();

    /**
    * @param string|DateTime $ts
    * @throws InvalidArgumentException
    * @return QueueItemInterface Chainable
    */
    public function set_processing_date($ts);

    /**
    * @return DateTime|null
    */
    public function processing_date();

    /**
    * @param string|DateTime $ts
    * @throws InvalidArgumentException
    * @return QueueItemInterface Chainable
    */
    public function set_processed_date($ts);

    /**
    * @return DateTime|null
    */
    public function processed_date();
}
