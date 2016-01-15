<?php

namespace Charcoal\Email\Queue;

use \InvalidArgumentException;

/**
* Full implementation, as a Trait, of the `QueueableInterface`.
*/
trait QueueableTrait
{
    /**
    * @var string $queue_id
    */
    private $queue_id;

    /**
    * @param string|null $queue_id
    * @throws InvalidArgumentException
    * @return QueueableInterface Chainable
    */
    public function set_queue_id($queue_id)
    {
        if ($queue_id === null) {
            $this->queue_id = null;
            return $this;
        }

        if (!is_string($queue_id)) {
            throw new InvalidArgumentException(
                'Queue ID must be a string'
            );
        }
        $this->queue_id = $queue_id;
        return $this;
    }

    /**
    * @return string $queue_id
    */
    public function queue_id()
    {
        return $this->queue_id;
    }

    /**
    * @param mixed $ts Date/time to set the queue processing time
    * @return mixed
    */
    abstract public function queue($ts = null);
}
