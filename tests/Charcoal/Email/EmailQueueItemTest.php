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

    public function setUp()
    {
        $this->obj = new EmailQueueItem([
            'logger' => new NullLogger()
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
