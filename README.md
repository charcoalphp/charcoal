Charcoal Queue
==============

Queue Managers, Queue Items and Queueable objects (through Interface & Trait) for Charcoal.

## How to install

```
composer require locomotivemtl/charcoal-queue`
```

## Dependencies

-   `locomotivemtl/charcoal-core` for the `CollectionLoader`

## Queueing System

Queue managers loop queue items. Queue items represent actions to be performed (as defined by the `process()` method).

## Queue Manager

The queue manager is available as an abstract class: `AbstractQueueManager`.
This class implements the `QueueManagerInterface`.

The queue can be identified with the `queue_id`. It can be set with `setQueueId()`.

The queue can be processed with `processQueue()`.
If for any reason the items need to be loaded, it can be done with `loadQueueItems()`.

There are 4 callbacks that can be defined:

-   `setProcessedCallback()`
-   `setItemCallback()`
-   `setItemSuccessCallbak()`
-   `setItemFailureCallback()`

There are only 1 abstract method:

-   `queueItemProto()` which must returns a `QueueItemInterface` instance

## Queue Items

Queue Items should implement the `QueueItemInterface`. This can be helped via the `QueueItemTrait`.

Queue items can be identified with a `queue_id`. (The same `queue_id` used by the queue manager).

Items can be processed with `process($callback, $successCallback, $failureCallback)`.

The queue item properties are:

-   `queue_id`
-   `queue_item_data`
-   `queued_date`
-   `processing_date`
-   `processed_date`
-   `processed`

## Queuable Objects

The `QueueableInterface` defines objects that can be queued. This interface is really simple and only provides:

-   `setQueueId()` which can be inherited from `QueueableTrait`
-   `queueId()` (`queue_id` getter) which can be inherited from `QueueableTrait`
-   `queue($ts = null)` which is abstract and must be written inside class which implement the queueable interface
