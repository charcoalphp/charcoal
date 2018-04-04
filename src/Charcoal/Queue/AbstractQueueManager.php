<?php

namespace Charcoal\Queue;

use Exception;

// From PSR-3
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

// From 'charcoal-core'
use Charcoal\Loader\CollectionLoader;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

/**
 * Abstract Queue Manager
 *
 * The queue manager is used to load queued items and batch-process them.
 *
 * ## Loading queued items
 *
 * If a "queue_id" is specified, only the item for this specific queue will be loaded.
 * Otherwise, all unprocessed queue items will be processed.
 *
 * ## Type of queue items
 *
 * The type of queue items can be set in extended concrete class with the
 * `queue_item_proto()` method. This method should return a QueueItemInterface instance.
 *
 * ## Callbacks
 *
 * There are 4 available callback methods that can be set:
 *
 * - `item_callback`
 *   - Called after an item has been processed.
 *   - Arguments: `QueueModelInterface $item`
 * - `item_success_callback`
 * - `item_failure_callback`
 * - `processed_callback`
 *   - Called when the entire queue has been processed
 *   - Arguments: `array $success`, `array $failures`
 */
abstract class AbstractQueueManager implements
    QueueManagerInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * The queue processing rate (throttle), in items per second.
     *
     * @var integer
     */
    private $rate = 0;

    /**
     * The batch limit.
     *
     * @var integer
     */
    private $limit = 0;

    /**
     * The queue ID.
     *
     * If set, then it will load only the items from this queue.
     * If NULL, load *all* queued items.
     *
     * @var mixed
     */
    private $queueId;

    /**
     * The callback routine when an item is processed (whether resolved or rejected).
     *
     * @var callable $itemCallback
     */
    private $itemCallback;

    /**
     * The callback routine when the item is resolved.
     *
     * @var callable $itemSuccessCallback
     */
    private $itemSuccessCallback;

    /**
     * The callback routine when the item is rejected.
     *
     * @var callable $itemFailureCallback
     */
    private $itemFailureCallback;

    /**
     * The callback routine when the queue is processed.
     *
     * @var callable $processedCallback
     */
    private $processedCallback;

    /**
     * @var FactoryInterface $queueItemFactory
     */
    private $queueItemFactory;

    /**
     * Construct new queue manager.
     *
     * @param array $data Dependencies and settings.
     */
    public function __construct(array $data = [])
    {
        $this->setLogger($data['logger']);
        $this->setQueueItemFactory($data['queue_item_factory']);

        if (isset($data['rate'])) {
            $this->rate = intval($data['rate']);
        }

        if (isset($data['limit'])) {
            $this->limit = intval($data['limit']);
        }
    }

    /**
     * Set the manager's data.
     *
     * @param array $data The queue data to set.
     * @return AbstractQueueManager Chainable
     */
    public function setData(array $data)
    {
        if (isset($data['queue_id']) && $data['queue_id']) {
            $this->setQueueId($data['queue_id']);
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
     * @param integer $rate The throttling / processing rate, in items per second.
     * @return self
     */
    public function setRate($rate)
    {
        $this->rate = intval($rate);
        return $this;
    }

    /**
     * @return integer
     */
    public function rate()
    {
        return $this->rate;
    }

    /**
     * @param integer $limit The maximum number of items to load.
     * @return self
     */
    public function setLimit($limit)
    {
        $this->limit = intval($limit);
        return $this;
    }

    /**
     * @return integer
     */
    public function limit()
    {
        return $this->limit;
    }

    /**
     * Set the callback routine when an item is processed.
     *
     * @param callable $callback A item callback routine.
     * @return self
     */
    public function setItemCallback(callable $callback)
    {
        $this->itemCallback = $callback;
        return $this;
    }

    /**
     * Set the callback routine when the item is resolved.
     *
     * @param callable $callback A item callback routine.
     * @return self
     */
    public function setItemSuccessCallback(callable $callback)
    {
        $this->itemSuccessCallback = $callback;
        return $this;
    }

    /**
     * Set the callback routine when the item is rejected.
     *
     * @param callable $callback A item callback routine.
     * @return self
     */
    public function setItemFailureCallback(callable $callback)
    {
        $this->itemSuccessCallback = $callback;
        return $this;
    }

    /**
     * Set the callback routine when the queue is processed.
     *
     * @param callable $callback A queue callback routine.
     * @return self
     */
    public function setProcessedCallback(callable $callback)
    {
        $this->processedCallback = $callback;
        return $this;
    }

    /**
     * Process the items of the queue.
     *
     * If no callback is passed and a self::$processedCallback is set, the latter is used.
     *
     * @param  callable $callback An optional alternative callback routine executed
     *                            after all queue items are processed.
     * @return boolean  Success / Failure
     */
    public function processQueue(callable $callback = null)
    {
        $queued = $this->loadQueueItems();

        if (!is_callable($callback)) {
            $callback = $this->processedCallback;
        }

        $success  = [];
        $failures = [];
        $skipped  = [];
        foreach ($queued as $q) {
            try {
                $res = $q->process($this->itemCallback, $this->itemSuccessCallback, $this->itemFailureCallback);
                if ($res === true) {
                    $success[] = $q;
                } elseif ($res === false) {
                    $failures[] = $q;
                } else {
                    $skipped[] = $q;
                }
            } catch (Exception $e) {
                $this->logger->error(
                    sprintf('Could not process a queue item: %s', $e->getMessage())
                );
                $failures[] = $q;
                continue;
            }

            // Throttle according to processing rate.
            if ($this->rate > 0) {
                usleep(1000000/$this->rate);
            }
        }

        if (is_callable($callback)) {
            $callback($success, $failures, $skipped);
        }

        return true;
    }

    /**
     * Retrieve the items of the current queue.
     *
     * @return \Charcoal\Model\Collection|array
     */
    public function loadQueueItems()
    {
        $loader = new CollectionLoader([
            'logger'  => $this->logger,
            'factory' => $this->queueItemFactory(),
        ]);
        $loader->setModel($this->queueItemProto());
        $loader->addFilter([
            'property' => 'processed',
            'value'    => 0,
        ]);
        $loader->addFilter([
             'property' => 'processing_date',
             'operator' => '<',
             'value'    => date('Y-m-d H:i:s'),
        ]);

        $queueId = $this->queueId();
        if ($queueId) {
            $loader->addFilter([
                'property' => 'queue_id',
                'value'    => $queueId,
            ]);
        }

        $loader->addOrder([
            'property' => 'queued_date',
            'mode'     => 'asc',
        ]);
        $queued = $loader->load();

        if ($this->limit > 0) {
            $loader->setNumPerPage($this->limit);
            $loader->setPage(0);
        }

        return $queued;
    }

    /**
     * Retrieve the queue item's model.
     *
     * @return QueueItemInterface
     */
    abstract public function queueItemProto();

    /**
     * @return FactoryInterface
     */
    protected function queueItemFactory()
    {
        return $this->queueItemFactory;
    }

    /**
     * @param FactoryInterface $factory The factory used to create queue items.
     * @return self
     */
    private function setQueueItemFactory(FactoryInterface $factory)
    {
        $this->queueItemFactory = $factory;
        return $this;
    }
}
