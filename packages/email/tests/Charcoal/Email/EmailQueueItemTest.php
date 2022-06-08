<?php

namespace Charcoal\Tests\Email;

use Charcoal\Email\EmailQueueItem;
use Charcoal\Queue\QueueItemInterface;
use Charcoal\Tests\AbstractTestCase;
use Psr\Log\NullLogger;

class EmailQueueItemTest extends AbstractTestCase
{
    /**
     * @var EmailQueueItem
     */
    public $obj;

    /**
     * Set up the test.
     *
     * @return void
     */
    public function setUp()
    {
        $this->obj = $this->createObj();
    }

    /**
     * Create tested class.
     *
     * @return EmailQueueItem
     */
    public function createObj()
    {
        return new EmailQueueItem([
            'logger' => new NullLogger()
        ]);
    }

    /**
     * @return void
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(QueueItemInterface::class, $this->obj);
    }
}
