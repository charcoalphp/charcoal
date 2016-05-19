<?php

namespace Charcoal\Email;

// Dependencies from `charcoal-queue`
use \Charcoal\Queue\AbstractQueueManager;

// Local namespace dependencies
use \Charcoal\Email\EmailQueueItem;

/**
 * Queue manager for emails.
 */
class EmailQueueManager extends AbstractQueueManager
{
    /**
     * Retrieve the queue item's model.
     *
     * @return EmailQueueItem
     */
    public function queueItemProto()
    {
        return $this->queueItemFactory()->create('charcoal/email/email-queue-item');
    }
}
