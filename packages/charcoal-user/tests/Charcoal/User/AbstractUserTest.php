<?php

namespace Charcoal\Tests\User;

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
     *
     * @return void
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
                    'logger'     => $container['logger'],
                    'translator' => $container['translator'],
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

    /**
     * @return void
     */
    public function testKey()
    {
        $obj = $this->obj;
        $this->assertEquals('id', $obj->key());
    }

    /**
     * @return void
     */
    public function testDefaultValues()
    {
        $obj = $this->obj;
        $this->assertTrue($obj['active']);
    }

    /**
     * Assert that the `setData` method:
     * - is chainable
     * - set the various properties
     *
     * @return void
     */
    public function testSetData()
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

    /**
     * @return void
     */
    public function testSetEmail()
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

    /**
     * @return void
     */
    public function testSetRoles()
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

    /**
     * @return void
     */
    public function testSetLastLoginDate()
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

    /**
     * @return void
     */
    public function testSetLastLoginIp()
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

    /**
     * @return void
     */
    public function testSetLastPasswordDate()
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

    /**
     * @return void
     */
    public function testSetLastPasswordIp()
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
