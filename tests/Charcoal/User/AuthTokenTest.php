<?php

namespace Charcoal\User\Tests;

use PHPUnit_Framework_TestCase;

use DateTime;

use Psr\Log\NullLogger;

use Charcoal\Model\Service\MetadataLoader;

use Charcoal\User\AuthToken;

/**
 *
 */
class AuthTokenTest extends PHPUnit_Framework_TestCase
{
    public $obj;

    /**
     *
     */
    public function setUp()
    {
        mb_internal_encoding('UTF-8');

        $container = $GLOBALS['container'];

        $metadataLoader = new MetadataLoader([
            'logger'    => new NullLogger(),
            'base_path' => __DIR__,
            'paths'     => ['metadata'],
            'config'    => $GLOBALS['container']['config'],
            'cache'     => $GLOBALS['container']['cache']
        ]);

        $this->obj = new AuthToken([
            'logger'            => new NullLogger(),
            'metadata_loader'   => $metadataLoader
        ]);
    }

    /**
     *
     */
    public function testSetKeyIsIdent()
    {
        $this->assertEquals('ident', $this->obj->key());
    }

    /**
     *
     */
    public function testSetIdent()
    {
        $ret = $this->obj->setIdent('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->ident());
    }

    /**
     *
     */
    public function testSetToken()
    {
        $ret = $this->obj->setToken('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->token());
    }

    /**
     *
     */
    public function testSetUsername()
    {
        $ret = $this->obj->setUsername('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->username());

        $this->setExpectedException('\Exception');
        $this->obj->setUsername([]);
    }

    /**
     *
     */
    public function testSetUsernameIsLowercase()
    {
        $this->obj->setUsername('FÔOBÄR');
        $this->assertEquals('fôobär', $this->obj->username());
    }

    /**
     *
     */
    public function testSetExpiry()
    {
        $date = new DateTime('tomorrow');
        $ret = $this->obj->setExpiry($date);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals($date, $this->obj->expiry());

        $this->setExpectedException('\Exception');
        $this->obj->setExpiry('fsdjkfsadg');
    }

    /**
     *
     */
    public function testSetCreated()
    {
        $date = new DateTime('tomorrow');
        $ret = $this->obj->setCreated($date);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals($date, $this->obj->created());

        $this->setExpectedException('\Exception');
        $this->obj->setCreated('fsdjkfsadg');
    }

    /**
     *
     */
    public function testSetLastModified()
    {
        $date = new DateTime('tomorrow');
        $ret = $this->obj->setLastModified($date);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals($date, $this->obj->lastModified());

        $this->setExpectedException('\Exception');
        $this->obj->setLastModified('fsdjkfsadg');
    }
}
