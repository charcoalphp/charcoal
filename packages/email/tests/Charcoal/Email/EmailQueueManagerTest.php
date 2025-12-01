<?php

namespace Charcoal\Tests\Email;

use Charcoal\Email\EmailQueueItem;
use Charcoal\Email\EmailQueueManager;
use Psr\Log\NullLogger;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(EmailQueueManager::class)]
class EmailQueueManagerTest extends AbstractEmailTestCase
{
    /**
     * @var EmailQueueManager
     */
    public $obj;

    protected function setUp(): void
    {
        $container = $this->container();
        $this->obj = new EmailQueueManager([
            'logger' => new NullLogger(),
            'queue_item_factory' => $container->get('model/factory')
        ]);
    }

    public function testProto()
    {
        $ret = $this->obj->queueItemProto();
        $this->assertInstanceOf(EmailQueueItem::class, $ret);
    }
}
