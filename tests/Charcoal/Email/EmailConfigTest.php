<?php

namespace Charcoals\Tests\Email;

use \Charcoal\App\Email\EmailConfig;

class EmailConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testSetData()
    {
        $obj = new EmailConfig();
        $data = [
            'smtp'             => true,
            'smtp_hostname'    => 'localhost',
            'default_from'     => 'test@example.com',
            'default_reply_to' => [
                'name'  => 'Test',
                'email' => 'test@example.com'
            ],
            'default_log'   => true,
            'default_track' => true
        ];
        $ret = $obj->setData($data);
        $this->assertSame($ret, $obj);
        $this->assertEquals(true, $obj->smtp());
        $this->assertEquals('localhost', $obj->smtpHostname());
        $this->assertEquals('test@example.com', $obj->defaultFrom());
        $this->assertEquals('"Test" <test@example.com>', $obj->defaultReplyTo());
        $this->assertEquals(true, $obj->defaultLog());
        $this->assertEquals(true, $obj->defaultTrack());
    }

    public function testSetSmtp()
    {
        $obj = new EmailConfig();
        $ret = $obj->setSmtp(true);
        $this->assertSame($ret, $obj);
        $this->assertEquals(true, $obj->smtp());

    }

    public function testSetDefaultFrom()
    {
        $obj = new EmailConfig();
        $ret = $obj->setDefaultFrom('test@example.com');
        $this->assertSame($ret, $obj);
        $this->assertEquals('test@example.com', $obj->defaultFrom());

        $obj->setDefaultFrom([
            'name'  => 'Test',
            'email' => 'test@example.com'
        ]);
        $this->assertEquals('"Test" <test@example.com>', $obj->defaultFrom());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setDefaultFrom(123);
    }

    public function testSetDefaultReplyTo()
    {
        $obj = new EmailConfig();
        $ret = $obj->setDefaultReplyTo('test@example.com');
        $this->assertSame($ret, $obj);
        $this->assertEquals('test@example.com', $obj->defaultReplyTo());

        $obj->setDefaultReplyTo([
            'name'  => 'Test',
            'email' => 'test@example.com'
        ]);
        $this->assertEquals('"Test" <test@example.com>', $obj->defaultReplyTo());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setDefaultReplyTo(123);
    }

    public function testSetDefaultLog()
    {
        $obj = new EmailConfig();
        $ret = $obj->setDefaultLog(true);
        $this->assertSame($ret, $obj);
        $this->assertEquals(true, $obj->defaultLog());
    }

    public function testSetDefaultTrack()
    {
        $obj = new EmailConfig();
        $ret = $obj->setDefaultTrack(true);
        $this->assertSame($ret, $obj);
        $this->assertEquals(true, $obj->defaultTrack());

    }
}
