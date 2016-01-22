<?php

namespace Charcoal\Email\Queue;

/**
 * The queue manager is used to load queued items and batch-process them.
 */
interface QueueManagerInterface
{
    /**
     * Set the queue's ID.
     *
     * @param mixed $id The unique queue identifier.
     * @return QueueManagerInterface Chainable
     */
    public function setQueueId($id);

    /**
     * Get the queue's ID.
     *
     * @return mixed
     */
    public function queueId();

    /**
     * Set the callback routine when the item is resolved.
     *
     * @param callable $callback A item callback routine.
     * @return QueueManagerInterface Chainable
     */
    public function setItemSuccessCallback(callable $callback);

    /**
     * Set the callback routine when the item is rejected.
     *
     * @param callable $callback A item callback routine.
     * @return QueueManagerInterface Chainable
     */
    public function setItemFailureCallback(callable $callback);

    /**
     * Set the callback routine when the queue is processed.
     *
     * @param callable $callback A queue callback routine.
     * @return QueueManagerInterface Chainable
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
     * @return Collection
     */
    public function loadQueueItems();

    /**
     * Retrieve the queue item's model.
     *
     * @return QueueItemInterface
     */
    public function queueItemProto();
}
