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
     * @return SmsQueueItem
     */
    public function queue_item_proto()
    {
        return new EmailQueueItem([
            'logger'=>$this->logger
        ]);
    }
}
