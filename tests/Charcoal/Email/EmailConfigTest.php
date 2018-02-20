<?php

namespace Charcoals\Tests\Email;

use PHPUnit_Framework_TestCase;

use InvalidArgumentException;

use Charcoal\Email\EmailConfig;

class EmailConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EmailConfig
     */
    public $obj;

    public function setUp()
    {
        $this->obj = new EmailConfig();
    }
    public function testSetData()
    {
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
        $ret = $this->obj->merge($data);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(true, $this->obj->smtp());
        $this->assertEquals('localhost', $this->obj->smtpHostname());
        $this->assertEquals('test@example.com', $this->obj->defaultFrom());
        $this->assertEquals('"Test" <test@example.com>', $this->obj->defaultReplyTo());
        $this->assertEquals(true, $this->obj->defaultLog());
        $this->assertEquals(true, $this->obj->defaultTrack());
    }

    public function testSetSmtp()
    {
        $ret = $this->obj->setSmtp(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj->smtp());

        $this->obj->setSmtp(false);
        $this->assertFalse($this->obj->smtp());
    }

    public function testSetSmtpHostname()
    {
        $ret = $this->obj->setSmtpHostname('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj->smtpHostname());

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setSmtpHostname([]);
    }

    public function testSetSmtpPort()
    {
        $ret = $this->obj->setSmtpPort(42);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(42, $this->obj->smtpPort());

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setSmtpPort('foo');
    }

    public function testSetSmtpAuth()
    {
        $ret = $this->obj->setSmtpAuth(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj->smtpAuth());

        $this->obj->setSmtpAuth(false);
        $this->assertFalse($this->obj->smtpAuth());
    }

    public function testSetSmtpUsername()
    {
        $ret = $this->obj->setSmtpUsername('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj->smtpUsername());

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setSmtpUsername([]);
    }

    public function testSetSmtpPassword()
    {
        $ret = $this->obj->setSmtpPassword('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj->smtpPassword());

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setSmtpPassword([]);
    }

    public function testSetDefaultFrom()
    {
        $ret = $this->obj->setDefaultFrom('test@example.com');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('test@example.com', $this->obj->defaultFrom());

        $this->obj->setDefaultFrom([
            'name'  => 'Test',
            'email' => 'test@example.com'
        ]);
        $this->assertEquals('"Test" <test@example.com>', $this->obj->defaultFrom());

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setDefaultFrom(123);
    }

    public function testSetDefaultReplyTo()
    {
        $ret = $this->obj->setDefaultReplyTo('test@example.com');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('test@example.com', $this->obj->defaultReplyTo());

        $this->obj->setDefaultReplyTo([
            'name'  => 'Test',
            'email' => 'test@example.com'
        ]);
        $this->assertEquals('"Test" <test@example.com>', $this->obj->defaultReplyTo());

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setDefaultReplyTo(123);
    }

    public function testSetDefaultLog()
    {
        $ret = $this->obj->setDefaultLog(true);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(true, $this->obj->defaultLog());
    }

    public function testSetDefaultTrack()
    {
        $ret = $this->obj->setDefaultTrack(true);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(true, $this->obj->defaultTrack());
    }
}
