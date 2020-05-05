<?php

namespace Charcoal\Tests\Email;

use Charcoal\Email\EmailQueueItem;
use Charcoal\Queue\QueueItemInterface;
use Charcoal\Tests\AbstractTestCase;

class EmailQueueItemTest extends AbstractTestCase
{
    /**
     * @var EmailQueueItem
     */
    public $obj;

    public function setUp()
    {
        $container = $GLOBALS['container'];
        $this->obj = new EmailQueueItem([
            'logger' => $container['logger']
        ]);
    }

    public function testSetData()
    {
        $ret = $this->obj->setData([
            'ident' => 'phpunit'
        ]);
        $this->assertSame($this->obj, $ret);

        $this->assertEquals('phpunit', $this->obj->ident());
    }
}
