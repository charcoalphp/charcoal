<?php

namespace Charcoal\Tests\Email\Objects;

use DateTime;

use Charcoal\Email\Objects\EmailLog;
use Charcoal\Tests\AbstractTestCase;
use Psr\Log\NullLogger;

class EmailLogTest extends AbstractTestCase
{
    /**
     * @var EmailLog
     */
    public $obj;

    public function setUp()
    {
        $this->obj = new EmailLog([
            'logger' => new NullLogger()
        ]);
    }

    public function testSetData()
    {
        $ret = $this->obj->setData([
            'queue_id'     => 'foo',
            'message_id'   => 'foobar',
            'campaign'     => 'phpunit',
            'to'           => 'phpunit@example.com',
            'from'         => 'charcoal@locomotive.ca',
            'subject'      => 'Foo bar',
            'send_ts'      => '2010-01-02 03:45:00',
        ]);
        $this->assertSame($this->obj, $ret);

        $this->assertEquals('foobar', $this->obj->messageId());
        $this->assertEquals('phpunit', $this->obj->campaign());
        $this->assertEquals('foo', $this->obj->queueId());
        $this->assertEquals('phpunit@example.com', $this->obj->to());
        $this->assertEquals('charcoal@locomotive.ca', $this->obj->from());
        $this->assertEquals('Foo bar', $this->obj->subject());
        $this->assertEquals(new DateTime('2010-01-02 03:45:00'), $this->obj->sendTs());
    }

    public function testKey()
    {
        $this->assertEquals('id', $this->obj->key());
    }
}
