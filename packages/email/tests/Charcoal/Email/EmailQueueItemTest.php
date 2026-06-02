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
     */
    protected function setUp(): void
    {
        $this->obj = $this->createObj();
    }

    /**
     * Create tested class.
     */
    public function createObj(): \Charcoal\Email\EmailQueueItem
    {
        return new EmailQueueItem([
            'logger' => new NullLogger()
        ]);
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(QueueItemInterface::class, $this->obj);
    }
}
