<?php

namespace Charcoal\Tests\Email;

use Charcoal\Email\EmailQueueItem;
use Charcoal\Email\EmailQueueManager;
use Charcoal\Tests\AbstractTestCase;
use Psr\Log\NullLogger;

/**
 * Class EmailQueueManagerTest
 */
class EmailQueueManagerTest extends AbstractTestCase
{
    /**
     * @var EmailQueueManager
     */
    public $obj;

    public function setUp()
    {
        $container = $GLOBALS['container'];
        $this->obj = new EmailQueueManager([
            'logger' => new NullLogger(),
            'queue_item_factory' => $container['model/factory']
        ]);
    }

    public function testProto()
    {
        $ret = $this->obj->queueItemProto();
        $this->assertInstanceOf(EmailQueueItem::class, $ret);
    }
}
