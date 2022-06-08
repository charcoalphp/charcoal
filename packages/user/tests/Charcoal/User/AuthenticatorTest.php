<?php

namespace Charcoal\Tests\User;

// From Pimple
use Pimple\Container;

// From 'charcoal-user'
use Charcoal\User\Authenticator;
use Charcoal\User\AuthToken;
use Charcoal\User\GenericUser as User;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\User\ContainerProvider;

/**
 *
 */
class AuthenticatorTest extends AbstractTestCase
{
    /**
     * Tested Class.
     *
     * @var Authenticator
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

        $this->obj = $this->createAuthenticator();
    }

    /**
     * Create a new Authenticator instance.
     *
     * @return Authenticator
     */
    public function createAuthenticator()
    {
        $container = $this->container();

        $authenticator = new Authenticator([
            'logger'        => $container['logger'],
            'user_type'     => User::class,
            'user_factory'  => $container['model/factory'],
            'token_type'    => AuthToken::class,
            'token_factory' => $container['model/factory'],
        ]);

        return $authenticator;
    }

    /**
     * Create a new User instance from a given Authenticator.
     *
     * @param  Authenticator $authenticator The authenticator service.
     * @return User
     */
    public function createUser(Authenticator $authenticator)
    {
        $factoryMethod = new ReflectionMethod(Authenticator, 'userFactory');

        return $factoryMethod->invoke($authenticator)->create(User::class);
    }

    /**
     * @return void
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(Authenticator::class, $this->obj);
    }

    /**
     * @return void
     */
    public function testAuthenticate()
    {
        $ret = $this->obj->authenticate();
        $this->assertNull($ret);
    }

    /**
     * @return void
     */
    public function testAuthenticateByPasswordInvalidEmail()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->obj->authenticateByPassword([], '');
    }

    /**
     * @return void
     */
    public function testAuthenticateByPasswordInvalidPassword()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->obj->authenticateByPassword('', []);
    }

    /**
     * @return void
     */
    public function testAuthenticateByPasswordEmpty()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->obj->authenticateByPassword('', '');
    }

    /**
     * @return void
     */
    public function testAuthenticateByPassword()
    {
        $this->assertNull($this->obj->authenticateByPassword('test', 'password'));
    }

    /**
     * @return void
     */
    /*
    public function testUpdateSession()
    {
        $obj = $this->obj;

        $sessionKey = $obj::sessionKey();
        $this->obj['id'] = 'foo';
        $this->obj->saveToSession();
        $this->assertEquals($_SESSION[$sessionKey], $this->obj['id']);
    }
    */

    /**
     * @return void
     */
    /*
    public function testResetPassword()
    {
        $ret = $this->obj->resetPassword('foo');
        $this->assertSame($ret, $this->obj);

        $this->obj['id'] = 'bar';

        $this->expectException(InvalidArgumentException::class);
        $this->obj->resetPassword(false);
    }
    */

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
