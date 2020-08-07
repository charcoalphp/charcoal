<?php

namespace Charcoal\Email;

// From 'charcoal-queue'
use Charcoal\Queue\AbstractQueueManager;

// From 'charcoal-email'
use Charcoal\Email\EmailQueueItem;

/**
 * Email Queue Manager
 */
class EmailQueueManager extends AbstractQueueManager
{
    /**
     * Retrieve the class name of the queue item model.
     *
     * @return string
     */
    public function getQueueItemClass(): string
    {
        return EmailQueueItem::class;
    }
}
