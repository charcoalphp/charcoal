<?php

namespace Charcoal\Email\Queue;

/**
* The queue manager is used to load queued items and batch-process them.
*/
interface QueueManagerInterface
{
    /**
    * @param mixed $id
    * @return QueueManagerInterface Chainable
    */
    public function set_queue_id($id);

    /**
    * @return mixed
    */
    public function queue_id();

    /**
    * @param callable $cb
    * @return QueueManagerInterface Chainable
    */
    public function set_item_failure_callback(callable $cb);

    /**
    * @param callable $cb
    * @return QueueManagerInterface Chainable
    */
    public function set_item_success_callback(callable $cb);

    /**
    * @param callable $cb
    * @return QueueManagerInterface Chainable
    */
    public function set_processed_callback(callable $cb);

    /**
    *
    */
    public function process_queue();

    /**
    * @return Collection
    */
    public function load_queue_items();

    /**
    * @return QueueItemInterface
    */
    public function queue_item_proto();
}
