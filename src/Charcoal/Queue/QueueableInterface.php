<?php

namespace Charcoal\Queue;

/**
* Queuable objects can be queued.
*/
interface QueueableInterface
{
    /**
    * @param string $queue_id
    * @return QueueableInterface Chainable
    */
    public function set_queue_id($queue_id);

    /**
    * @return string $queue_id
    */
    public function queue_id();

    /**
    * @param mixed $ts Date/time to set the queue processing time
    * @return mixed
    */
    public function queue($ts = null);
}
