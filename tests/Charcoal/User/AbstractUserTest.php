<?php

namespace Charcoal\User\Tests;

use DateTime;

// From PHPUnit
use PHPUnit_Framework_TestCase;

// From Pimple
use Pimple\Container;

// From 'charcoal-user'
use Charcoal\User\AbstractUser;
use Charcoal\User\UserInterface;
use Charcoal\User\Tests\ContainerProvider;

/**
 *
 */
class AbstractUserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tested Class.
     *
     * @var UserInterface
     */
    private $obj;

    /**
     * Store the service container.
     *
     * @var Container
     */
    private $container;

    /**
     * Set up the test.
     */
    public function setUp()
    {
        if (session_id()) {
            session_unset();
        }

        $container = $this->container();

        $this->obj = $this->getMockForAbstractClass(
            AbstractUser::class,
            [
                [
                    # 'container'        => $container,
                    'logger'           => $container['logger'],
                    'translator'       => $container['translator'],
                    # 'property_factory' => $container['property/factory'],
                    # 'metadata_loader'  => $container['metadata/loader']
                ]
            ],
            '',
            true,
            true,
            true,
            [ 'sessionKey' ]
        );

        $this->obj->expects($this->any())
            ->method('sessionKey')
            ->will($this->returnValue('charcoal.user'));
    }

    public function testKey()
    {
        $obj = $this->obj;
        $this->assertEquals('username', $obj->key());
    }

    public function testDefaultValues()
    {
        $obj = $this->obj;
        $this->assertTrue($obj->active());
        $this->assertEquals('', $obj->loginToken());
    }

    /**
     * Assert that the `setData` method:
     * - is chainable
     * - set the various properties
     */
    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData([
            'username'   => 'Foo',
            'email'      => 'test@example.com',
            'roles'      => [ 'foo', 'bar' ],
            'loginToken' => 'token',
            'active'     => false
        ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->username());
        $this->assertEquals('test@example.com', $obj->email());
        $this->assertEquals('token', $obj->loginToken());
        $this->assertFalse($obj->active());
    }

    /*public function testSetDataDoesNotSetPassword()
    {
        $obj = $this->obj;
        $this->assertNull($obj->password());
        $obj->setData(['password'=>'password123']);
        $this->assertNull($obj->password())
    }*/

    public function testSetUsername()
    {
        $ret = $this->obj->setUsername('Foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj->username());

        $this->obj['username'] = 'Baz';
        $this->assertEquals('baz', $this->obj->username());

        $this->obj->set('username', 'FOO');
        $this->assertEquals('foo', $this->obj['username']);

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setUsername(false);
    }

    public function testIdIsUsername()
    {
        $this->assertequals('username', $this->obj->key());
        $this->obj->setUsername('foo');
        $this->assertEquals('foo', $this->obj->id());
        $this->assertEquals($this->obj->id(), $this->obj->username());
    }

    public function testSetEmail()
    {
        $ret = $this->obj->setEmail('test@example.com');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('test@example.com', $this->obj->email());

        $this->obj['email'] = 'foo@example.com';
        $this->assertEquals('foo@example.com', $this->obj->email());

        $this->obj->set('email', 'bar@example.com');
        $this->assertEquals('bar@example.com', $this->obj['email']);

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setEmail(false);
    }

    public function testSetRoles()
    {
        $ret = $this->obj->setRoles(null);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals([], $this->obj->roles());

        $this->obj->setRoles('foo, bar');
        $this->assertEquals(['foo', 'bar'], $this->obj->roles());

        $this->obj->setRoles(['foobar', 'baz']);
        $this->assertEquals(['foobar', 'baz'], $this->obj->roles());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setRoles(42);
    }

    public function testSetLastLoginDate()
    {
        $ret = $this->obj->setLastLoginDate('today');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(new DateTime('today'), $this->obj->lastLoginDate());

        $this->obj->setLastLoginDate(null);
        $this->assertNull($this->obj->lastLoginDate());

        $date = new DateTime('tomorrow');
        $this->obj->setLastLoginDate($date);
        $this->assertEquals($date, $this->obj->lastLoginDate());

        $date2 = new DateTime('today');
        $this->obj['last_login_date'] = $date2;
        $this->assertEquals($date2, $this->obj->lastLoginDate());

        $this->obj->set('last_login_date', $date);
        $this->assertEquals($date, $this->obj['last_login_date']);

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setLastLoginDate(false);
    }


    public function testSetLastLoginIp()
    {
        $ret = $this->obj->setLastLoginIp('8.8.8.8');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('8.8.8.8', $this->obj->lastLoginIp());

        $this->obj['last_login_ip'] = '1.2.3.4';
        $this->assertEquals('1.2.3.4', $this->obj->lastLoginIp());

        $this->obj->set('last_login_ip', '4.3.2.1');
        $this->assertEquals('4.3.2.1', $this->obj['last_login_ip']);

        $this->obj->setLastLoginIp(null);
        $this->assertNull($this->obj['lastLoginIp']);

        $this->obj['lastLoginIp'] = 134744072;
        $this->assertEquals('8.8.8.8', $this->obj->lastLoginIp());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setLastLoginIp(false);
    }

    public function testSetLastPasswordDate()
    {
        $ret = $this->obj->setLastPasswordDate('today');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(new DateTime('today'), $this->obj->lastPasswordDate());

        $this->obj->setLastPasswordDate(null);
        $this->assertNull($this->obj->lastPasswordDate());

        $date = new DateTime('tomorrow');
        $this->obj->setLastPasswordDate($date);
        $this->assertEquals($date, $this->obj->lastPasswordDate());

        $date2 = new DateTime('today');
        $this->obj['last_password_date'] = $date2;
        $this->assertEquals($date2, $this->obj->lastPasswordDate());

        $this->obj->set('last_password_date', $date);
        $this->assertEquals($date, $this->obj['last_password_date']);

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setLastPasswordDate(false);
    }

    public function testSetLastPasswordIp()
    {
        $ret = $this->obj->setLastPasswordIp('8.8.8.8');
        $this->assertSame($ret, $this->obj);

        $this->assertEquals('8.8.8.8', $this->obj->lastPasswordIp());

        $this->obj['last_password_ip'] = '1.2.3.4';
        $this->assertEquals('1.2.3.4', $this->obj->lastPasswordIp());

        $this->obj->set('last_password_ip', '4.3.2.1');
        $this->assertEquals('4.3.2.1', $this->obj['last_password_ip']);

        $this->obj->setLastPasswordIp(null);
        $this->assertNull($this->obj['lastPasswordIp']);

        $this->obj['lastPasswordIp'] = 134744072;
        $this->assertEquals('8.8.8.8', $this->obj->lastPasswordIp());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setLastPasswordIp(false);
    }

    public function testSetLoginToken()
    {
        $ret = $this->obj->setLoginToken('abc');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('abc', $this->obj->loginToken());

        $this->obj['login_token'] = 'foo';
        $this->assertEquals('foo', $this->obj->loginToken());

        $this->obj->set('login_token', 'bar');
        $this->assertEquals('bar', $this->obj['login_token']);

        $this->obj['loginToken'] = null;
        $this->assertNull($this->obj['loginToken']);

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setLoginToken([]);
    }

    public function testLoginFailedResetUsername()
    {
        $this->obj->loginFailed('foo');
        $this->assertEquals('', $this->obj->username());
    }

    public function testResetPassword()
    {
        $ret = $this->obj->resetPassword('foo');
        $this->assertSame($ret, $this->obj);

        $this->obj['username'] = 'bar';
        //$ret = $this->obj->resetPassword('foo');

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->resetPassword(false);
    }

    /**
     * Set up the service container.
     *
     * @return Container
     */
    private function container()
    {
        if ($this->container === null) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerBaseServices($container);
            $containerProvider->registerModelFactory($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
