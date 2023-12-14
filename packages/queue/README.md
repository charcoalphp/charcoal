Charcoal Queue
==============

The Queue package provides an abstract queue service to defer the processing of time consuming tasks.

## Installation

```shell
composer require charcoal/queue
```

## Usage

### Queueing System

Queue managers loop queue items. Queue items represent actions to be performed (as defined by the `process()` method).

### Queue Manager

The queue manager is available as an abstract class: `AbstractQueueManager`.
This class implements the `QueueManagerInterface`.

The processing speed (throttle) can be controlled via the `rate` property, in items per second.

The batch limit (number of items to process per iteration) can be controlled with the `limit` property.

The queue can be identified with the `queue_id`. It can be set with `setQueueId()`.

The queue can be processed with `processQueue()`.
If for any reason the items need to be loaded, it can be done with `loadQueueItems()`.

There are 4 callbacks that can be defined:

* `setProcessedCallback()`
* `setItemCallback()`
* `setItemSuccessCallbak()`
* `setItemFailureCallback()`

There are only 1 abstract method:

* `queueItemProto()` which must returns a `QueueItemInterface` instance

### Queue Items

Queue Items should implement the `QueueItemInterface`. This can be helped via the `QueueItemTrait`.

Queue items can be identified with a `queue_id`. (The same `queue_id` used by the queue manager).

Items can be processed with `process($callback, $successCallback, $failureCallback)`.

The queue item properties are:

* `queue_id`
* `queue_item_data`
* `queued_date`
* `processing_date`
* `processed_date`
* `processed`

### Queuable Objects

The `QueueableInterface` defines objects that can be queued. This interface is really simple and only provides:

* `setQueueId()` which can be inherited from `QueueableTrait`
* `queueId()` (`queue_id` getter) which can be inherited from `QueueableTrait`
* `queue($ts = null)` which is abstract and must be written inside class which implement the queueable interface

## Resources

* [Contributing](https://github.com/charcoalphp/.github/blob/main/CONTRIBUTING.md)
* [Report issues](https://github.com/charcoalphp/charcoal/issues) and
  [send pull requests](https://github.com/charcoalphp/charcoal/pulls)
  in the [main Charcoal repository](https://github.com/charcoalphp/charcoal)
