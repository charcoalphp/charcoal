<?php

namespace Charcoal\Queue;

/**
 * The queue manager is used to load queued items and batch-process them.
 */
interface QueueManagerInterface
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
     * @return mixed
     */
    public function queueId();

    /**
     * @param integer $rate The throttling / processing rate, in items per second.
     * @return self
     */
    public function setRate($rate);

    /**
     * @return integer
     */
    public function rate();

    /**
     * @param integer $limit The maximum number of items to load.
     * @return self
     */
    public function setLimit($limit);

    /**
     * @return integer
     */
    public function limit();

    /**
     * Set the callback routine when the item is resolved.
     *
     * @param callable $callback A item callback routine.
     * @return self
     */
    public function setItemSuccessCallback(callable $callback);

    /**
     * Set the callback routine when the item is rejected.
     *
     * @param callable $callback A item callback routine.
     * @return self
     */
    public function setItemFailureCallback(callable $callback);

    /**
     * Set the callback routine when the queue is processed.
     *
     * @param callable $callback A queue callback routine.
     * @return self
     */
    public function setProcessedCallback(callable $callback);

    /**
     * Process the items of the queue.
     *
     * @return boolean Success / Failure
     */
    public function processQueue();

    /**
     * Retrieve the items of the current queue.
     *
     * @return \Charcoal\Model\Collection|array
     */
    public function loadQueueItems();

    /**
     * Retrieve the queue item's model.
     *
     * @return QueueItemInterface
     */
    public function queueItemProto();
}
