<?php

namespace Charcoal\Tests\User;

use Charcoal\User\GenericUser;
use DateTime;
use InvalidArgumentException;

// From Pimple
use Pimple\Container;

// From 'charcoal-user'
use Charcoal\User\AbstractUser;
use Charcoal\User\UserInterface;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\User\ContainerProvider;

/**
 *
 */
class AbstractUserTest extends AbstractTestCase
{
    /**
     * Tested Class.
     */
    private \Charcoal\User\UserInterface $obj;

    /**
     * Store the service container.
     */
    private ?\Pimple\Container $container = null;

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        if (session_id()) {
            session_unset();
        }

        $container = $this->container();

        $this->obj = new class ([
            'logger'     => $container['logger'],
            'translator' => $container['translator'],
        ]) extends AbstractUser {
            public static function sessionKey(): string
            {
                return 'charcoal.user';
            }
        };
    }

    public function testKey(): void
    {
        $obj = $this->obj;
        $this->assertEquals('id', $obj->key());
    }

    public function testDefaultValues(): void
    {
        $obj = $this->obj;
        $this->assertTrue($obj['active']);
    }

    /**
     * Assert that the `setData` method:
     * - is chainable
     * - set the various properties
     */
    public function testSetData(): void
    {
        $obj = $this->obj;
        $ret = $obj->setData([
            'id'         => 'foo',
            'email'      => 'test@example.com',
            'roles'      => [ 'foo', 'bar' ],
            'active'     => false
        ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj['id']);
        $this->assertEquals('test@example.com', $obj['email']);
        $this->assertFalse($obj['active']);
    }

    public function testSetEmail(): void
    {
        $ret = $this->obj->setEmail('test@example.com');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('test@example.com', $this->obj['email']);

        $this->obj['email'] = 'foo@example.com';
        $this->assertEquals('foo@example.com', $this->obj['email']);

        $this->obj->set('email', 'bar@example.com');
        $this->assertEquals('bar@example.com', $this->obj['email']);

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setEmail(false);
    }

    public function testSetRoles(): void
    {
        $ret = $this->obj->setRoles(null);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals([], $this->obj['roles']);

        $this->obj->setRoles('foo, bar');
        $this->assertEquals(['foo', 'bar'], $this->obj['roles']);

        $this->obj->setRoles(['foobar', 'baz']);
        $this->assertEquals(['foobar', 'baz'], $this->obj['roles']);

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setRoles(42);
    }

    public function testSetLastLoginDate(): void
    {
        $ret = $this->obj->setLastLoginDate('today');
        $this->assertSame($ret, $this->obj);
        $date = new DateTime('today');
        $this->assertEquals($date, $this->obj['lastLoginDate']);

        $this->obj->setLastLoginDate(null);
        $this->assertNull($this->obj['lastLoginDate']);

        $date = new DateTime('tomorrow');
        $this->obj->setLastLoginDate($date);
        $this->assertEquals($date, $this->obj['lastLoginDate']);

        $date2 = new DateTime('today');
        $this->obj['last_login_date'] = $date2;
        $this->assertEquals($date2, $this->obj['lastLoginDate']);

        $this->obj->set('last_login_date', $date);
        $this->assertEquals($date, $this->obj['lastLoginDate']);

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setLastLoginDate(false);
    }

    public function testSetLastLoginIp(): void
    {
        $ret = $this->obj->setLastLoginIp('8.8.8.8');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('8.8.8.8', $this->obj['lastLoginIp']);

        $this->obj['last_login_ip'] = '1.2.3.4';
        $this->assertEquals('1.2.3.4', $this->obj['lastLoginIp']);

        $this->obj->set('last_login_ip', '4.3.2.1');
        $this->assertEquals('4.3.2.1', $this->obj['lastLoginIp']);

        $this->obj->setLastLoginIp(null);
        $this->assertNull($this->obj['lastLoginIp']);

        $this->obj['lastLoginIp'] = 134744072;
        $this->assertEquals('8.8.8.8', $this->obj['lastLoginIp']);

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setLastLoginIp(false);
    }

    public function testSetLastPasswordDate(): void
    {
        $ret = $this->obj->setLastPasswordDate('today');
        $this->assertSame($ret, $this->obj);
        $date = new DateTime('today');
        $this->assertEquals($date, $this->obj['lastPasswordDate']);

        $this->obj->setLastPasswordDate(null);
        $this->assertNull($this->obj['lastPasswordDate']);

        $date = new DateTime('tomorrow');
        $this->obj->setLastPasswordDate($date);
        $this->assertEquals($date, $this->obj['lastPasswordDate']);

        $date2 = new DateTime('today');
        $this->obj['last_password_date'] = $date2;
        $this->assertEquals($date2, $this->obj['lastPasswordDate']);

        $this->obj->set('last_password_date', $date);
        $this->assertEquals($date, $this->obj['last_password_date']);

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setLastPasswordDate(false);
    }

    public function testSetLastPasswordIp(): void
    {
        $ret = $this->obj->setLastPasswordIp('8.8.8.8');
        $this->assertSame($ret, $this->obj);

        $this->assertEquals('8.8.8.8', $this->obj['lastPasswordIp']);

        $this->obj['last_password_ip'] = '1.2.3.4';
        $this->assertEquals('1.2.3.4', $this->obj['lastPasswordIp']);

        $this->obj->set('last_password_ip', '4.3.2.1');
        $this->assertEquals('4.3.2.1', $this->obj['last_password_ip']);

        $this->obj->setLastPasswordIp(null);
        $this->assertNull($this->obj['lastPasswordIp']);

        $this->obj['lastPasswordIp'] = 134744072;
        $this->assertEquals('8.8.8.8', $this->obj['lastPasswordIp']);

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setLastPasswordIp(false);
    }

    /**
     * Set up the service container.
     */
    private function container(): \Pimple\Container
    {
        if (!$this->container instanceof \Pimple\Container) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerBaseServices($container);
            $containerProvider->registerModelFactory($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
