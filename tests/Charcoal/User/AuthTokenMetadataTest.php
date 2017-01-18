<?php

namespace Charcoal\Tests\User;

use PHPUnit_Framework_TestCase;

use Charcoal\User\AuthTokenMetadata;

/**
 *
 */
class AuthTokenMetadataTest extends PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new AuthTokenMetadata();
    }

    public function testDefaults()
    {
        $this->assertTrue($this->obj->enabled());
        $this->assertEquals('charcoal_user_login', $this->obj->cookieName());
        $this->assertEquals('15 days', $this->obj->cookieDuration());
        $this->assertFalse($this->obj->httpsOnly());
    }

    public function testSetEnabled()
    {
        $ret = $this->obj->setEnabled(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->enabled());
    }

    public function testSetCookieName()
    {
        $ret = $this->obj->setCookieName('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj->cookieName());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setCookieName(false);
    }

    public function testSetCookieDuration()
    {
        $ret = $this->obj->setCookieDuration('2 month');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('2 month', $this->obj->cookieDuration());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setCookieDuration(false);
    }
}
