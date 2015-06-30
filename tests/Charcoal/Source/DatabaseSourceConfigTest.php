<?php

namespace Charcoal\Tests\Source;

use \Charcoal\Source\DatabaseSourceConfig as DatabaseSourceConfig;
use \Charcoal\Encoder\EncoderFactory as EncoderFactory;

class DatabaseSourceConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultData()
    {
        $obj = new DatabaseSourceConfig();
        $defaults = $obj->default_data();

        $this->assertEquals('mysql', $obj->type());
        $this->assertEquals($obj->type(), $defaults['type']);
        $this->assertEquals($obj->hostname(), $defaults['hostname']);
    }

    public function testSetData()
    {
        $obj = new DatabaseSourceConfig();
        $ret = $obj->set_data([]);
        $this->assertSame($ret, $obj);

        # $this->setExpectedException('\InvalidArgumentException');
        $this->setExpectedException('\PHPUnit_Framework_Error');
        $obj->set_data(false);
    }

    public function testSetHostname()
    {
        $obj = new DatabaseSourceConfig();
        $this->assertEquals('localhost', $obj->hostname());
        $ret = $obj->set_hostname('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->hostname());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_hostname(false);
    }

    public function testSetUsername()
    {
        $obj = new DatabaseSourceConfig();
        $this->assertEquals(null, $obj->username());
        $ret = $obj->set_username('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->username());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_username(false);
    }

    public function testSetPassword()
    {
        $obj = new DatabaseSourceConfig();
        $this->assertEquals('', $obj->password());
        $ret = $obj->set_password('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->password());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_password(false);
    }

    public function testSetPasswordEncoding()
    {
        $obj = new DatabaseSourceConfig();
        $this->assertEquals(null, $obj->password_encoding());
        $ret = $obj->set_password_encoding('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->password_encoding());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_password_encoding(false);
    }

    public function testSetPasswordSalt()
    {
        $obj = new DatabaseSourceConfig();
        $this->assertEquals(null, $obj->password_salt());
        $ret = $obj->set_password_salt('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->password_salt());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_password_salt(false);
    }

    public function testEncodedPassword()
    {
        $plain_password = 'foobar123';
        $salt = 'barbaz987';
        $encoder = EncoderFactory::instance()->get('base64');
        $encoded = $encoder->encode($plain_password, $salt);

        $obj = new DatabaseSourceConfig();
        $obj->set_password_encoding('base64');
        $obj->set_password_salt($salt);
        $obj->set_password($encoded);
        $this->assertEquals($plain_password, $obj->password());
    }

    public function testSetDatabase()
    {
        $obj = new DatabaseSourceConfig();
        $this->assertEquals(null, $obj->database());
        $ret = $obj->set_database('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->database());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_database(false);
    }

    public function testSetDisableUtf8()
    {
        $obj = new DatabaseSourceConfig();
        $this->assertEquals(false, $obj->disable_utf8());
        $ret = $obj->set_disable_utf8(true);
        $this->assertSame($ret, $obj);
        $this->assertEquals(true, $obj->disable_utf8());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_disable_utf8('foo');
    }
}
