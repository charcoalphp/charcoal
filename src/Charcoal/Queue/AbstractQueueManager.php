<?php

namespace Charcoal\Queue;

// From `charcoal-core`
use \Charcoal\Loader\CollectionLoader;

/**
* Abstract Queue Manager
*
* The queue manager is used to load queued items and batch-process them.
*
* ## Loading queued items
* If a "queue_id" is specified, only the item for this specific queue will be loaded.
* Otherwise, all unprocessed queue items will be processed.
*
* ## Type of queue items
* The type of queue items can be set in extended concrete class with the
* `queue_item_proto()` method. This method should return a QueueItemInterface instance.
*
* ## Callbacks
* There are 4 available callback methods that can be set:
* - `item_callback`
*   - Called after an item has been processed.
*   - Arguments: [`QueueModelInterface $item`]
* - `item_success_callback`
* - `item_failure_callback`
* - `processed_callback`
*   - Called when the entire queue has been processed
*   - Arguments: [`array $success`, array $failures]
*/
abstract class AbstractQueueManager implements QueueManagerInterface
{
    /**
    * If it is set, then it will load only the items from this queue.
    * If null, load *all* queued items.
    * @var mixed $queue_id
    */
    private $queue_id;

    /**
    * @var callable $_item_callback
    */
    private $item_callback;
    /**
    * @var callable $_item_success_callback
    */
    private $item_success_callback;
    /**
    * @var callable $_item_failure_callback
    */
    private $item_failure_callback;

    /**
    * @var $_processed_callback
    */
    private $processed_callback;

    public function set_data(array $data)
    {
        if (isset($data['queue_id']) && $data['queue_id']) {
            $this->set_queue_id($data['queue_id']);
        }
        return $this;
    }

    /**
    * @param mixed $id
    * @return AbstractQueueManager Chainable
    */
    public function set_queue_id($id)
    {
        $this->queue_id = $id;
        return $this;
    }

    /**
    * @return mixed
    */
    public function queue_id()
    {
        return $this->queue_id;
    }

    /**
    * @param callable $cb
    * @return QueueManagerInterface Chainable
    */
    public function set_item_callback(callable $cb)
    {
        $this->item_callback = $cb;
        return $this;
    }

    /**
    * @param callable $cb
    * @return QueueManagerInterface Chainable
    */
    public function set_item_success_callback(callable $cb)
    {
        $this->item_success_callback = $cb;
        return $this;
    }

    /**
    * @param callable $cb
    * @return QueueManagerInterface Chainable
    */
    public function set_item_failure_callback(callable $cb)
    {
        $this->item_success_callback = $cb;
        return $this;
    }

    /**
    * @param callable $cb
    * @return QueueManagerInterface Chainable
    */
    public function set_processed_callback(callable $cb)
    {
        $this->processed_callback = $cb;
        return $this;
    }

    /**
    * @param callable $callback Callback after all queue items are processed.
    * @return boolean Success / Failure
    */
    public function process_queue($callback = null)
    {
        $queued = $this->load_queue_items();

        $cb = ($callback !== null) ? $callback : $this->processed_callback;

        $success = [];
        $failures = [];
        $skipped = [];
        foreach ($queued as $q) {
            $res = $q->process($this->item_callback, $this->item_success_callback, $this->item_failure_callback);
            if ($res === true) {
                $success[] = $q;
            } elseif ($res === false) {
                $failures[] = $q;
            } else {
                $skipped[] = $q;
            }
        }
        if ($this->processed_callback !== null) {
            $cb = $this->processed_callback;
            $cb($success, $failures, $skipped);
        }
        return true;
    }

    /**
    * @return Collection
    */
    public function load_queue_items()
    {

        $loader = new CollectionLoader();
        $loader->set_model($this->queue_item_proto());
        $loader->add_filter([
            'property'=>'processed',
            'val'=>0
        ]);
        $loader->add_filter([
             'property'=>'processing_date',
             'val'=>date('Y-m-d H:i:s'),
             'operator'=>'<'
        ]);

        $queue_id = $this->queue_id();
        if ($queue_id) {
            $loader->add_filter([
                'property'=>'queue_id',
                'val'=>$queue_id
            ]);
        }

        $loader->add_order([
            'property'=>'queued_date',
            'mode'=>'asc'
        ]);
        $queued = $loader->load();

        return $queued;
    }

    /**
    * @return QueueItemInterface
    */
    abstract public function queue_item_proto();
}
