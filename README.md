Charcoal Queue
==============

Queue, Queue Items and Queueable objects (through Interface & Trait) for Charcoal.

## How to install

```
composer require locomotivemtl/charcoal-queue`
```

## Dependencies

- `locomotivemtl/charcoal-core` for the CollectionLoader

## Queueing system

Queue managers loop queue items. Queue items represent actions to be performed (as defined by the `process()` method.)

## Queue Manager

The queue manager is available as an abstract class: `AbstractQueueManager`.
This class implements the `QueueManagerInterface`

The queue can be identified with the `queue_id`. It can be set with `set_queue_id()`.

The queue can be processed with `process_queue()`.
If for any reason the items need to be loaded, it can be done with `load_queue_items()`

There are 4 callbacks that can be defined:

- `set_processed_callback()`
- `set_item_callback()`
- `set_item_success_callbak()`
- `set_item_failure_callback()`

There are only 1 abstract method:

- `queue_item_proto()` which must returns a `QueueItemInterface` instance

## Queue Items

Queue Items should implement the `QueueItemInterface`. This can be helped via the `QueueItemTrait`.

Queue items can be identified with a `queue_id`. (The same queue_id used by the queue manager).

Items can be processed with `process($cb, $success_cb, $failure_cb)`.

The queue item properties are:

- `queue_id`
- `queue_item_data`
- `queued_date`
- `processing_date`
- `processed_date`
- `processed`

## Queuable Objects

The `QueueableInterface` defines objects that can be queued. This interface is really simple and only provides:

- `set_queue_id()` which can be inherited from `QueueableTrait`
- `queue_id()` (queue_id getter) which can be inherited from `QueueableTrait`
- `queue($ts=null)` which is abstract and must be written inside class which implement the queueable interface
